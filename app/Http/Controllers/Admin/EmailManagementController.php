<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailNotification;
use Illuminate\Http\Request;

class EmailManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailNotification::query();

        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        $notifications = $query->latest()->get();

        if ($notifications->isEmpty()) {
            $this->createDefaultNotifications();
            $notifications = EmailNotification::latest()->get();
        }

        return view('admin.emails.index', compact('notifications'));
    }

    public function edit($id)
    {
        $notification = EmailNotification::findOrFail($id);
        return view('admin.emails.edit', compact('notification'));
    }

    public function update(Request $request, $id)
    {
        $notification = EmailNotification::findOrFail($id);
        $validated = $request->validate([
            'subject_line' => 'required|string|max:255',
            'email_body' => 'nullable|string',
            'recipients' => 'required|array',
            'recipients.*' => 'string',
            'is_enabled' => 'boolean',
        ]);

        $validated['is_enabled'] = $request->has('is_enabled');

        $notification->update($validated);
        return redirect()->route('admin.emails.index')->with('success', 'Email notification updated successfully.');
    }

    public function toggleStatus($id)
    {
        $notification = EmailNotification::findOrFail($id);
        $notification->update(['is_enabled' => !$notification->is_enabled]);
        return back()->with('success', 'Email notification status updated.');
    }

    private function createDefaultNotifications()
    {
        $defaults = [
            [
                'event_type' => 'exhibitor_registration',
                'subject_line' => 'Welcome to Event Name!',
                'email_body' => 'Thank you for registering as an exhibitor.',
                'recipients' => ['Exhibitor Contact', 'Attendee'],
                'category' => 'event_triggered',
                'is_enabled' => true,
            ],
            [
                'event_type' => 'booking_confirmation',
                'subject_line' => 'Booking Confirmed',
                'email_body' => 'Your booking has been confirmed.',
                'recipients' => ['Exhibitor Contact'],
                'category' => 'event_triggered',
                'is_enabled' => true,
            ],
            [
                'event_type' => 'payment_received',
                'subject_line' => 'Payment Received',
                'email_body' => 'We have received your payment.',
                'recipients' => ['Exhibitor Contact'],
                'category' => 'event_triggered',
                'is_enabled' => true,
            ],
            [
                'event_type' => 'document_approved',
                'subject_line' => 'Document Approved',
                'email_body' => 'Your document has been approved.',
                'recipients' => ['Exhibitor Contact'],
                'category' => 'event_triggered',
                'is_enabled' => true,
            ],
            [
                'event_type' => 'system_maintenance',
                'subject_line' => 'System Maintenance Notice',
                'email_body' => 'The system will be under maintenance.',
                'recipients' => ['All Users'],
                'category' => 'system',
                'is_enabled' => true,
            ],
        ];

        foreach ($defaults as $default) {
            EmailNotification::firstOrCreate(
                ['event_type' => $default['event_type']],
                $default
            );
        }
    }
}
