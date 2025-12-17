<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $folder = $request->get('folder', 'inbox'); // inbox, sent, archived, deleted
        
        // Get all messages where user is sender or receiver
        $messages = Message::where(function($query) use ($user) {
            $query->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
        })
        ->with(['sender', 'receiver'])
        ->orderBy('created_at', 'desc')
        ->get();

        // Filter messages based on folder
        if ($folder === 'sent') {
            $filteredMessages = $messages->where('sender_id', $user->id)->where('status', '!=', 'deleted');
        } elseif ($folder === 'archived') {
            $filteredMessages = $messages->where('status', 'archived');
        } elseif ($folder === 'deleted') {
            $filteredMessages = $messages->where('status', 'deleted');
        } else {
            // Inbox: active messages (not archived, not deleted)
            $filteredMessages = $messages->where('status', '!=', 'archived')->where('status', '!=', 'deleted');
        }

        // Build chat-style threads
        if ($folder === 'archived') {
            // In archived view, show each archived message as its own entry
            // so all previous chats are visible, not merged into one
            $threads = $filteredMessages
                ->sortByDesc('created_at')
                ->map(function ($message) use ($user) {
                    $otherUser = $message->sender_id === $user->id
                        ? $message->receiver
                        : $message->sender;

                    return [
                        'last_message' => $message,
                        'other_user' => $otherUser,
                        'unread_count' => 0,
                    ];
                })
                ->values();
        } else {
            // Inbox / Sent / Deleted: group by other participant (like WhatsApp)
            $threads = $filteredMessages
                ->groupBy(function ($message) use ($user) {
                    // Group by the other user's id
                    return $message->sender_id === $user->id
                        ? $message->receiver_id
                        : $message->sender_id;
                })
                ->map(function ($group) use ($user) {
                    $lastMessage = $group->sortByDesc('created_at')->first();
                    $otherUser = $lastMessage->sender_id === $user->id
                        ? $lastMessage->receiver
                        : $lastMessage->sender;

                    $unreadCount = $group->where('receiver_id', $user->id)
                        ->where('is_read', false)
                        ->count();

                    return [
                        'last_message' => $lastMessage,
                        'other_user' => $otherUser,
                        'unread_count' => $unreadCount,
                    ];
                })
                ->values();
        }

        // Get super admin user (first admin user)
        $superAdmin = User::role('Admin')->first();
        if (!$superAdmin) {
            $superAdmin = User::where('id', 1)->first();
        }

        return view('frontend.messages.index', [
            'messages' => $messages,
            'threads' => $threads,
            'superAdmin' => $superAdmin,
            'folder' => $folder,
        ]);
    }

    /**
     * Get new chat interface for super admin
     * Always creates a new conversation, doesn't merge with existing ones
     */
    public function newChat()
    {
        // Get super admin user (first admin user)
        $superAdmin = User::role('Admin')->first();
        if (!$superAdmin) {
            $superAdmin = User::where('id', 1)->first();
        }

        if (!$superAdmin) {
            return response()->json(['error' => 'Super admin not found'], 404);
        }

        return view('frontend.messages.new-chat', compact('superAdmin'));
    }

    public function create()
    {
        // Get super admin user (first admin user)
        $superAdmin = User::role('Admin')->first();
        if (!$superAdmin) {
            $superAdmin = User::where('id', 1)->first();
        }
        
        // Check if there's an existing conversation
        $existingConversation = Message::where(function($query) use ($superAdmin) {
            $query->where('sender_id', auth()->id())
                  ->where('receiver_id', $superAdmin->id);
        })->orWhere(function($query) use ($superAdmin) {
            $query->where('sender_id', $superAdmin->id)
                  ->where('receiver_id', auth()->id());
        })->orderBy('created_at', 'desc')->first();

        if ($existingConversation) {
            // Redirect to existing conversation
            return redirect()->route('messages.show', $existingConversation->id);
        }

        // Return new chat view
        return view('frontend.messages.create', compact('superAdmin'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:5000',
            'exhibition_id' => 'nullable|exists:exhibitions,id',
        ]);

        $userId = auth()->id();
        $receiverId = $request->receiver_id;
        $isNewChat = $request->input('is_new_chat', false);

        // If this is explicitly a new chat (from new chat interface),
        // archive any existing active conversation between these two users
        if ($isNewChat) {
            Message::where(function ($query) use ($userId, $receiverId) {
                    $query->where(function ($q) use ($userId, $receiverId) {
                        $q->where('sender_id', $userId)
                          ->where('receiver_id', $receiverId);
                    })->orWhere(function ($q) use ($userId, $receiverId) {
                        $q->where('sender_id', $receiverId)
                          ->where('receiver_id', $userId);
                    });
                })
                ->where('status', '!=', 'archived')
                ->where('status', '!=', 'deleted')
                ->update(['status' => 'archived']);
        }

        // Create new message
        $message = Message::create([
            'sender_id' => $userId,
            'receiver_id' => $receiverId,
            'exhibition_id' => $request->exhibition_id,
            'message' => $request->message,
            'status' => 'inbox',
            'is_read' => false,
        ]);

        // Create notification for receiver
        $sender = User::find($userId);
        $receiver = User::find($receiverId);
        
        if ($receiver && $sender) {
            // Determine notification message based on sender role
            $senderName = $sender->hasRole('Admin') ? 'Super Admin' : $sender->name;
            
            \App\Models\Notification::create([
                'user_id' => $receiverId,
                'type' => 'message',
                'title' => 'New Message',
                'message' => 'You have notification from ' . $senderName,
                'notifiable_type' => \App\Models\Message::class,
                'notifiable_id' => $message->id,
            ]);
        }

        // If AJAX request, return JSON response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully.',
                'message_id' => $message->id,
            ]);
        }

        return redirect()->route('messages.index')->with('success', 'Message sent successfully.');
    }

    public function show(string $id)
    {
        $userId = auth()->id();

        $message = Message::where(function($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
        })
        ->with(['sender', 'receiver'])
        ->findOrFail($id);

        // Determine the other participant in this conversation
        $otherUserId = $message->sender_id === $userId
            ? $message->receiver_id
            : $message->sender_id;

        // Load full conversation between the two users (thread-style)
        $conversation = Message::where(function ($query) use ($userId, $otherUserId) {
                $query->where(function ($q) use ($userId, $otherUserId) {
                    $q->where('sender_id', $userId)
                      ->where('receiver_id', $otherUserId);
                })->orWhere(function ($q) use ($userId, $otherUserId) {
                    $q->where('sender_id', $otherUserId)
                      ->where('receiver_id', $userId);
                });
            })
            ->orderBy('created_at')
            ->get();

        // Mark all messages received by the user in this conversation as read
        Message::whereIn('id', $conversation->where('receiver_id', $userId)
                ->where('is_read', false)
                ->pluck('id'))
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $otherUser = User::find($otherUserId);

        return view('frontend.messages.show', compact('conversation', 'otherUser'));
    }

    public function edit(string $id)
    {
        $message = Message::where('sender_id', auth()->id())
            ->where('status', '!=', 'archived')
            ->findOrFail($id);
        
        $admins = User::role('Admin')->orWhere('id', 1)->get();
        return view('frontend.messages.edit', compact('message', 'admins'));
    }

    public function update(Request $request, string $id)
    {
        $message = Message::where('sender_id', auth()->id())
            ->where('status', '!=', 'archived')
            ->findOrFail($id);

        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $message->update([
            'message' => $request->message,
            'status' => 'inbox', // Reset status
        ]);

        return redirect()->route('messages.index')->with('success', 'Message updated successfully.');
    }

    public function archive(string $id)
    {
        $userId = auth()->id();
        $message = Message::where(function($query) use ($userId) {
            $query->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
        })->findOrFail($id);

        // Archive all messages in this conversation thread
        $otherUserId = $message->sender_id === $userId
            ? $message->receiver_id
            : $message->sender_id;

        Message::where(function ($query) use ($userId, $otherUserId) {
            $query->where(function ($q) use ($userId, $otherUserId) {
                $q->where('sender_id', $userId)
                  ->where('receiver_id', $otherUserId);
            })->orWhere(function ($q) use ($userId, $otherUserId) {
                $q->where('sender_id', $otherUserId)
                  ->where('receiver_id', $userId);
            });
        })
        ->where('status', '!=', 'archived')
        ->update(['status' => 'archived']);

        return back()->with('success', 'Conversation archived successfully.');
    }

    public function archiveBulk(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id',
        ]);

        $userId = auth()->id();
        
        // Archive all messages in the selected conversations
        foreach ($request->message_ids as $messageId) {
            $message = Message::where(function($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })->find($messageId);

            if (!$message) {
                continue; // Skip if message not found
            }

            $otherUserId = $message->sender_id === $userId
                ? $message->receiver_id
                : $message->sender_id;

            Message::where(function ($query) use ($userId, $otherUserId) {
                $query->where(function ($q) use ($userId, $otherUserId) {
                    $q->where('sender_id', $userId)
                      ->where('receiver_id', $otherUserId);
                })->orWhere(function ($q) use ($userId, $otherUserId) {
                    $q->where('sender_id', $otherUserId)
                      ->where('receiver_id', $userId);
                });
            })
            ->where('status', '!=', 'archived')
            ->where('status', '!=', 'deleted')
            ->update(['status' => 'archived']);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Conversations archived successfully.']);
        }

        return back()->with('success', 'Conversations archived successfully.');
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id',
        ]);

        $userId = auth()->id();
        
        // Get all messages in the selected conversations
        foreach ($request->message_ids as $messageId) {
            $message = Message::where(function($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })->find($messageId);

            if (!$message) {
                continue; // Skip if message not found
            }

            $otherUserId = $message->sender_id === $userId
                ? $message->receiver_id
                : $message->sender_id;

            // Mark all unread messages in this conversation as read
            Message::where(function ($query) use ($userId, $otherUserId) {
                $query->where(function ($q) use ($userId, $otherUserId) {
                    $q->where('sender_id', $userId)
                      ->where('receiver_id', $otherUserId);
                })->orWhere(function ($q) use ($userId, $otherUserId) {
                    $q->where('sender_id', $otherUserId)
                      ->where('receiver_id', $userId);
                });
            })
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Messages marked as read.']);
        }

        return back()->with('success', 'Messages marked as read.');
    }

    public function delete(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id',
        ]);

        $userId = auth()->id();
        
        // Delete all messages in the selected conversations
        foreach ($request->message_ids as $messageId) {
            $message = Message::where(function($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })->find($messageId);

            if (!$message) {
                continue; // Skip if message not found
            }

            $otherUserId = $message->sender_id === $userId
                ? $message->receiver_id
                : $message->sender_id;

            // Check if message is already deleted - if so, permanently delete (hard delete)
            // Otherwise, mark as deleted (soft delete)
            $conversationQuery = Message::where(function ($query) use ($userId, $otherUserId) {
                $query->where(function ($q) use ($userId, $otherUserId) {
                    $q->where('sender_id', $userId)
                      ->where('receiver_id', $otherUserId);
                })->orWhere(function ($q) use ($userId, $otherUserId) {
                    $q->where('sender_id', $otherUserId)
                      ->where('receiver_id', $userId);
                });
            });

            if ($message->status === 'deleted') {
                // Permanently delete
                $conversationQuery->delete();
            } else {
                // Soft delete - mark as deleted
                $conversationQuery->update(['status' => 'deleted']);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Conversations deleted successfully.']);
        }

        return back()->with('success', 'Conversations deleted successfully.');
    }

    public function unarchive(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id',
        ]);

        $userId = auth()->id();
        
        // Unarchive all messages in the selected conversations
        foreach ($request->message_ids as $messageId) {
            $message = Message::where(function($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })->find($messageId);

            if (!$message) {
                continue; // Skip if message not found
            }

            $otherUserId = $message->sender_id === $userId
                ? $message->receiver_id
                : $message->sender_id;

            // Unarchive all messages in this conversation
            Message::where(function ($query) use ($userId, $otherUserId) {
                $query->where(function ($q) use ($userId, $otherUserId) {
                    $q->where('sender_id', $userId)
                      ->where('receiver_id', $otherUserId);
                })->orWhere(function ($q) use ($userId, $otherUserId) {
                    $q->where('sender_id', $otherUserId)
                      ->where('receiver_id', $userId);
                });
            })
            ->where('status', 'archived')
            ->where('status', '!=', 'deleted')
            ->update(['status' => 'inbox']);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Conversations unarchived successfully.']);
        }

        return back()->with('success', 'Conversations unarchived successfully.');
    }
}
