<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    /**
     * Show form for super admin to start a new chat with any exhibitor.
     */
    public function create()
    {
        $exhibitors = User::role('Exhibitor')->orderBy('name')->get();

        return view('admin.communications.create', compact('exhibitors'));
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

        Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $exhibitor->id,
            'exhibition_id' => null,
            'message' => $data['message'],
            'status' => 'inbox',
            'is_closed' => false,
        ]);

        return redirect()
            ->route('admin.exhibitors.show', $exhibitor->id)
            ->with('success', 'Chat started with ' . $exhibitor->name . '.');
    }
}
