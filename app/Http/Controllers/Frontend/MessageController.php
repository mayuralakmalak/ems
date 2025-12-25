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
            // Inbox / Sent / Deleted: group by thread_id to show separate threads
            $threads = $filteredMessages
                ->groupBy('thread_id')
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
                        'thread_id' => $lastMessage->thread_id,
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

        // Always return new chat view - each compose creates a new thread
        return view('frontend.messages.create', compact('superAdmin'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:5000',
            'exhibition_id' => 'nullable|exists:exhibitions,id',
            'thread_id' => 'nullable|string',
        ]);

        $userId = auth()->id();
        $receiverId = $request->receiver_id;
        $isNewChat = $request->input('is_new_chat', false);
        $threadId = $request->input('thread_id');

        // Determine thread_id
        if ($isNewChat || empty($threadId)) {
            // Create a new unique thread_id for new chats
            $threadId = uniqid('thread_', true) . '_' . time();
        } else {
            // Use existing thread_id when replying
            // Verify the thread_id belongs to a conversation between these two users
            $existingThread = Message::where('thread_id', $threadId)
                ->where(function ($query) use ($userId, $receiverId) {
                    $query->where(function ($q) use ($userId, $receiverId) {
                        $q->where('sender_id', $userId)
                          ->where('receiver_id', $receiverId);
                    })->orWhere(function ($q) use ($userId, $receiverId) {
                        $q->where('sender_id', $receiverId)
                          ->where('receiver_id', $userId);
                    });
                })
                ->first();
            
            if (!$existingThread) {
                // Thread doesn't exist or doesn't belong to this conversation, create new one
                $threadId = uniqid('thread_', true) . '_' . time();
            }
        }

        // Create new message
        $message = Message::create([
            'thread_id' => $threadId,
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

        // Always return JSON for AJAX requests, or if X-Requested-With header is present
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully.',
                'message_id' => $message->id,
                'thread_id' => $threadId,
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

        // Load full conversation for this specific thread only
        $threadId = $message->thread_id;
        
        // If thread_id is missing (shouldn't happen after migration, but handle it)
        if (empty($threadId)) {
            // Fallback: get all messages between these two users (old behavior)
            $conversation = Message::where(function ($query) use ($userId, $otherUserId) {
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
            ->orderBy('created_at')
            ->get();
        } else {
            $conversation = Message::where('thread_id', $threadId)
                ->where('status', '!=', 'archived')
                ->where('status', '!=', 'deleted')
                ->orderBy('created_at')
                ->get();
        }

        // Mark all messages received by the user in this conversation as read
        Message::whereIn('id', $conversation->where('receiver_id', $userId)
                ->where('is_read', false)
                ->pluck('id'))
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $otherUser = User::find($otherUserId);

        return view('frontend.messages.show', compact('conversation', 'otherUser', 'message', 'threadId'));
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

        // Archive all messages in this thread
        if ($message->thread_id) {
            Message::where('thread_id', $message->thread_id)
                ->where('status', '!=', 'archived')
                ->where('status', '!=', 'deleted')
                ->update(['status' => 'archived']);
        }

        return back()->with('success', 'Conversation archived successfully.');
    }

    public function archiveBulk(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id',
        ]);

        $userId = auth()->id();
        
        // Archive all messages in the selected threads
        foreach ($request->message_ids as $messageId) {
            $message = Message::where(function($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })->find($messageId);

            if (!$message || !$message->thread_id) {
                continue; // Skip if message not found or has no thread_id
            }

            Message::where('thread_id', $message->thread_id)
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
        
        // Get all messages in the selected threads
        foreach ($request->message_ids as $messageId) {
            $message = Message::where(function($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })->find($messageId);

            if (!$message || !$message->thread_id) {
                continue; // Skip if message not found or has no thread_id
            }

            // Mark all unread messages in this thread as read
            Message::where('thread_id', $message->thread_id)
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
        
        // Delete all messages in the selected threads
        foreach ($request->message_ids as $messageId) {
            $message = Message::where(function($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })->find($messageId);

            if (!$message || !$message->thread_id) {
                continue; // Skip if message not found or has no thread_id
            }

            // Check if message is already deleted - if so, permanently delete (hard delete)
            // Otherwise, mark as deleted (soft delete)
            $threadQuery = Message::where('thread_id', $message->thread_id);

            if ($message->status === 'deleted') {
                // Permanently delete
                $threadQuery->delete();
            } else {
                // Soft delete - mark as deleted
                $threadQuery->update(['status' => 'deleted']);
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
        
        // Unarchive all messages in the selected threads
        foreach ($request->message_ids as $messageId) {
            $message = Message::where(function($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })->find($messageId);

            if (!$message || !$message->thread_id) {
                continue; // Skip if message not found or has no thread_id
            }

            // Unarchive all messages in this thread
            Message::where('thread_id', $message->thread_id)
                ->where('status', 'archived')
                ->where('status', '!=', 'deleted')
                ->update(['status' => 'inbox']);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Conversations unarchived successfully.']);
        }

        return back()->with('success', 'Conversations unarchived successfully.');
    }

    /**
     * Get new messages for a thread (for real-time updates)
     */
    public function getNewMessages($threadId, Request $request)
    {
        $userId = auth()->id();
        $lastMessageId = $request->input('last_message_id', 0);

        // Get messages in this thread that are newer than the last message
        $newMessages = Message::where('thread_id', $threadId)
            ->where('id', '>', $lastMessageId)
            ->where(function($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })
            ->where('status', '!=', 'archived')
            ->where('status', '!=', 'deleted')
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark received messages as read
        if ($newMessages->where('receiver_id', $userId)->where('is_read', false)->count() > 0) {
            Message::whereIn('id', $newMessages->where('receiver_id', $userId)
                    ->where('is_read', false)
                    ->pluck('id'))
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }

        return response()->json([
            'success' => true,
            'messages' => $newMessages->map(function($message) use ($userId) {
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'receiver_id' => $message->receiver_id,
                    'message' => $message->message,
                    'created_at' => $message->created_at->setTimezone('Asia/Kolkata')->format('M d, Y, h:i A'),
                    'is_user' => $message->sender_id === $userId,
                    'sender_name' => $message->sender_id === $userId ? 'You' : ($message->sender->name ?? 'Admin'),
                ];
            }),
            'last_message_id' => $newMessages->max('id') ?? $lastMessageId,
        ]);
    }

    /**
     * Get updated inbox list (for real-time updates)
     */
    public function getInboxUpdates(Request $request)
    {
        $user = auth()->user();
        $folder = $request->get('folder', 'inbox');
        $lastUpdateTime = $request->input('last_update_time', 0);
        
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
            $filteredMessages = $messages->where('status', '!=', 'archived')->where('status', '!=', 'deleted');
        }

        // Build chat-style threads
        if ($folder === 'archived') {
            $threads = $filteredMessages
                ->sortByDesc('created_at')
                ->map(function ($message) use ($user) {
                    $otherUser = $message->sender_id === $user->id
                        ? $message->receiver
                        : $message->sender;

                    return [
                        'id' => $message->id,
                        'last_message' => $message->message,
                        'last_message_preview' => \Str::limit($message->message, 50),
                        'other_user_name' => $otherUser->name ?? 'Admin',
                        'other_user_id' => $otherUser->id ?? null,
                        'created_at' => $message->created_at->setTimezone('Asia/Kolkata')->format('M d, Y'),
                        'unread_count' => 0,
                    ];
                })
                ->values();
        } else {
            $threads = $filteredMessages
                ->groupBy('thread_id')
                ->map(function ($group) use ($user) {
                    $lastMessage = $group->sortByDesc('created_at')->first();
                    $otherUser = $lastMessage->sender_id === $user->id
                        ? $lastMessage->receiver
                        : $lastMessage->sender;

                    $unreadCount = $group->where('receiver_id', $user->id)
                        ->where('is_read', false)
                        ->count();

                    return [
                        'id' => $lastMessage->id,
                        'last_message' => $lastMessage->message,
                        'last_message_preview' => \Str::limit($lastMessage->message, 50),
                        'other_user_name' => $otherUser->name ?? 'Admin',
                        'other_user_id' => $otherUser->id ?? null,
                        'created_at' => $lastMessage->created_at->setTimezone('Asia/Kolkata')->format('M d, Y'),
                        'unread_count' => $unreadCount,
                        'thread_id' => $lastMessage->thread_id,
                    ];
                })
                ->values();
        }

        $unreadCount = $messages->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->where('status', '!=', 'archived')
            ->where('status', '!=', 'deleted')
            ->count();

        return response()->json([
            'success' => true,
            'threads' => $threads,
            'unread_count' => $unreadCount,
            'inbox_count' => $messages->where('receiver_id', $user->id)->where('status', '!=', 'archived')->where('status', '!=', 'deleted')->count(),
            'sent_count' => $messages->where('sender_id', $user->id)->where('status', '!=', 'archived')->where('status', '!=', 'deleted')->count(),
            'archived_count' => $messages->where('status', 'archived')->count(),
            'deleted_count' => $messages->where('status', 'deleted')->count(),
        ]);
    }
}
