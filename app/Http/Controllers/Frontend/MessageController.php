<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get all messages where user is sender or receiver
        $messages = Message::where(function($query) use ($user) {
            $query->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
        })
        ->with(['sender', 'receiver'])
        ->orderBy('created_at', 'desc')
        ->get();

        // Build chat-style threads grouped by other participant (like WhatsApp)
        $activeMessages = $messages->where('status', '!=', 'archived');

        $threads = $activeMessages
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

        // Get admin users for messaging
        $admins = User::role('Admin')->orWhere('id', 1)->get();

        return view('frontend.messages.index', [
            'messages' => $messages,
            'admins' => $admins,
            'threads' => $threads,
        ]);
    }

    public function create()
    {
        $admins = User::role('Admin')->orWhere('id', 1)->get();
        return view('frontend.messages.create', compact('admins'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:5000',
            'exhibition_id' => 'nullable|exists:exhibitions,id',
        ]);

        Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'exhibition_id' => $request->exhibition_id,
            'message' => $request->message,
            'status' => 'inbox',
        ]);

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
        $message = Message::where(function($query) {
            $query->where('sender_id', auth()->id())
                  ->orWhere('receiver_id', auth()->id());
        })->findOrFail($id);

        $message->update(['status' => 'archived']);

        return back()->with('success', 'Message archived successfully.');
    }
}
