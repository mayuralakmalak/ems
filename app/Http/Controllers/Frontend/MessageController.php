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
        ->latest()
        ->get();

        // Get admin users for messaging
        $admins = User::role('Admin')->orWhere('id', 1)->get();

        return view('frontend.messages.index', compact('messages', 'admins'));
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
        $message = Message::where(function($query) {
            $query->where('sender_id', auth()->id())
                  ->orWhere('receiver_id', auth()->id());
        })
        ->with(['sender', 'receiver'])
        ->findOrFail($id);

        // Mark as read if user is receiver
        if ($message->receiver_id === auth()->id() && !$message->is_read) {
            $message->update(['is_read' => true, 'read_at' => now()]);
        }

        return view('frontend.messages.show', compact('message'));
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
