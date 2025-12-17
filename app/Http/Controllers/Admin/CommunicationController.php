<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    /**
     * Show all chats for super admin (Community Center).
     */
    public function index(Request $request)
    {
        $admin = auth()->user();
        $folder = $request->get('folder', 'inbox'); // inbox, sent, archived, deleted
        
        // Get all messages where admin is sender or receiver
        $messages = Message::where(function($query) use ($admin) {
            $query->where('sender_id', $admin->id)
                  ->orWhere('receiver_id', $admin->id);
        })
        ->with(['sender', 'receiver'])
        ->orderBy('created_at', 'desc')
        ->get();

        // Filter messages based on folder
        if ($folder === 'sent') {
            $filteredMessages = $messages->where('sender_id', $admin->id)->where('status', '!=', 'deleted');
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
                ->map(function ($message) use ($admin) {
                    $otherUser = $message->sender_id === $admin->id
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
                ->groupBy(function ($message) use ($admin) {
                    // Group by the other user's id
                    return $message->sender_id === $admin->id
                        ? $message->receiver_id
                        : $message->sender_id;
                })
                ->map(function ($group) use ($admin) {
                    $lastMessage = $group->sortByDesc('created_at')->first();
                    $otherUser = $lastMessage->sender_id === $admin->id
                        ? $lastMessage->receiver
                        : $lastMessage->sender;

                    $unreadCount = $group->where('receiver_id', $admin->id)
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

        return view('admin.communications.index', [
            'messages' => $messages,
            'threads' => $threads,
            'folder' => $folder,
        ]);
    }

    /**
     * Show conversation with a specific exhibitor.
     */
    public function show(string $id)
    {
        $adminId = auth()->id();

        $message = Message::where(function($query) use ($adminId) {
                $query->where('sender_id', $adminId)
                      ->orWhere('receiver_id', $adminId);
        })
        ->with(['sender', 'receiver'])
        ->findOrFail($id);

        // Determine the other participant in this conversation
        $otherUserId = $message->sender_id === $adminId
            ? $message->receiver_id
            : $message->sender_id;

        // Load full conversation between the two users (thread-style)
        // Only show non-archived messages in active conversation
        $conversation = Message::where(function ($query) use ($adminId, $otherUserId) {
                $query->where(function ($q) use ($adminId, $otherUserId) {
                    $q->where('sender_id', $adminId)
                      ->where('receiver_id', $otherUserId);
                })->orWhere(function ($q) use ($adminId, $otherUserId) {
                    $q->where('sender_id', $otherUserId)
                      ->where('receiver_id', $adminId);
                });
            })
            ->where('status', '!=', 'archived')
            ->orderBy('created_at')
            ->get();

        // Mark all messages received by the admin in this conversation as read
        Message::whereIn('id', $conversation->where('receiver_id', $adminId)
                ->where('is_read', false)
                ->pluck('id'))
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $otherUser = User::find($otherUserId);

        return view('admin.communications.show', compact('conversation', 'otherUser'));
    }

    /**
     * Get exhibitors list for new chat (AJAX)
     */
    public function create()
    {
        $exhibitors = User::role('Exhibitor')->orderBy('name')->get();

        // Check if this is an AJAX request
        if (request()->ajax() || request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest' || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'exhibitors' => $exhibitors->map(function($exhibitor) {
                    return [
                        'id' => $exhibitor->id,
                        'name' => $exhibitor->name,
                        'email' => $exhibitor->email,
                        'company_name' => $exhibitor->company_name ?? 'No company',
                    ];
                })
            ]);
        }

        return view('admin.communications.create', compact('exhibitors'));
    }

    /**
     * Get exhibitors list as JSON (dedicated endpoint)
     */
    public function getExhibitorsList()
    {
        $exhibitors = User::role('Exhibitor')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'exhibitors' => $exhibitors->map(function($exhibitor) {
                return [
                    'id' => $exhibitor->id,
                    'name' => $exhibitor->name,
                    'email' => $exhibitor->email,
                    'company_name' => $exhibitor->company_name ?? 'No company',
                ];
            })
        ]);
    }

    /**
     * Get new chat interface for selected exhibitor
     */
    public function newChat($exhibitorId)
    {
        $exhibitor = User::role('Exhibitor')->findOrFail($exhibitorId);
        $admin = auth()->user();

        return view('admin.communications.new-chat', compact('exhibitor', 'admin'));
    }

    /**
     * Store the first message of a new chat from admin to exhibitor.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'exhibitor_id' => 'required|exists:users,id',
            'message' => 'required|string|max:5000',
        ]);

        $exhibitor = User::role('Exhibitor')->findOrFail($data['exhibitor_id']);
        $adminId = auth()->id();
        $isNewChat = $request->input('is_new_chat', false);

        // If this is explicitly a new chat (from new chat interface),
        // archive any existing active conversation between admin and this exhibitor
        if ($isNewChat) {
            Message::where(function ($query) use ($adminId, $exhibitor) {
                    $query->where(function ($q) use ($adminId, $exhibitor) {
                        $q->where('sender_id', $adminId)
                          ->where('receiver_id', $exhibitor->id);
                    })->orWhere(function ($q) use ($adminId, $exhibitor) {
                        $q->where('sender_id', $exhibitor->id)
                          ->where('receiver_id', $adminId);
                    });
                })
                ->where('status', '!=', 'archived')
                ->where('status', '!=', 'deleted')
                ->update(['status' => 'archived']);
        }

        // Create new message
        $message = Message::create([
            'sender_id' => $adminId,
            'receiver_id' => $exhibitor->id,
            'exhibition_id' => null,
            'message' => $data['message'],
            'status' => 'inbox',
            'is_closed' => false,
            'is_read' => false,
        ]);

        // Create notification for exhibitor
        $admin = User::find($adminId);
        
        if ($exhibitor) {
            \App\Models\Notification::create([
                'user_id' => $exhibitor->id,
                'type' => 'message',
                'title' => 'New Message',
                'message' => 'You have notification from Super Admin',
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

        return redirect()
            ->route('admin.communications.index')
            ->with('success', 'Message sent to ' . $exhibitor->name . '.');
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id',
        ]);

        $adminId = auth()->id();
        
        // Get all messages in the selected conversations
        foreach ($request->message_ids as $messageId) {
            $message = Message::where(function($query) use ($adminId) {
                $query->where('sender_id', $adminId)
                      ->orWhere('receiver_id', $adminId);
            })->find($messageId);

            if (!$message) {
                continue; // Skip if message not found
            }

            $otherUserId = $message->sender_id === $adminId
                ? $message->receiver_id
                : $message->sender_id;

            // Mark all unread messages in this conversation as read
            Message::where(function ($query) use ($adminId, $otherUserId) {
                $query->where(function ($q) use ($adminId, $otherUserId) {
                    $q->where('sender_id', $adminId)
                      ->where('receiver_id', $otherUserId);
                })->orWhere(function ($q) use ($adminId, $otherUserId) {
                    $q->where('sender_id', $otherUserId)
                      ->where('receiver_id', $adminId);
                });
            })
            ->where('receiver_id', $adminId)
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

        $adminId = auth()->id();
        
        // Delete all messages in the selected conversations
        foreach ($request->message_ids as $messageId) {
            $message = Message::where(function($query) use ($adminId) {
                $query->where('sender_id', $adminId)
                      ->orWhere('receiver_id', $adminId);
            })->find($messageId);

            if (!$message) {
                continue; // Skip if message not found
            }

            $otherUserId = $message->sender_id === $adminId
                ? $message->receiver_id
                : $message->sender_id;

            // Check if message is already deleted - if so, permanently delete (hard delete)
            // Otherwise, mark as deleted (soft delete)
            $conversationQuery = Message::where(function ($query) use ($adminId, $otherUserId) {
                $query->where(function ($q) use ($adminId, $otherUserId) {
                    $q->where('sender_id', $adminId)
                      ->where('receiver_id', $otherUserId);
                })->orWhere(function ($q) use ($adminId, $otherUserId) {
                    $q->where('sender_id', $otherUserId)
                      ->where('receiver_id', $adminId);
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

    public function archive(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id',
        ]);

        $adminId = auth()->id();
        
        // Archive all conversations
        foreach ($request->message_ids as $messageId) {
            $message = Message::where(function($query) use ($adminId) {
                $query->where('sender_id', $adminId)
                      ->orWhere('receiver_id', $adminId);
            })->find($messageId);

            if (!$message) {
                continue; // Skip if message not found
            }

            $otherUserId = $message->sender_id === $adminId
                ? $message->receiver_id
                : $message->sender_id;

            Message::where(function ($query) use ($adminId, $otherUserId) {
                $query->where(function ($q) use ($adminId, $otherUserId) {
                    $q->where('sender_id', $adminId)
                      ->where('receiver_id', $otherUserId);
                })->orWhere(function ($q) use ($adminId, $otherUserId) {
                    $q->where('sender_id', $otherUserId)
                      ->where('receiver_id', $adminId);
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

    public function unarchive(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id',
        ]);

        $adminId = auth()->id();
        
        // Unarchive all messages in the selected conversations
        foreach ($request->message_ids as $messageId) {
            $message = Message::where(function($query) use ($adminId) {
                $query->where('sender_id', $adminId)
                      ->orWhere('receiver_id', $adminId);
            })->find($messageId);

            if (!$message) {
                continue; // Skip if message not found
            }

            $otherUserId = $message->sender_id === $adminId
                ? $message->receiver_id
                : $message->sender_id;

            // Unarchive all messages in this conversation
            Message::where(function ($query) use ($adminId, $otherUserId) {
                $query->where(function ($q) use ($adminId, $otherUserId) {
                    $q->where('sender_id', $adminId)
                      ->where('receiver_id', $otherUserId);
                })->orWhere(function ($q) use ($adminId, $otherUserId) {
                    $q->where('sender_id', $otherUserId)
                      ->where('receiver_id', $adminId);
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
