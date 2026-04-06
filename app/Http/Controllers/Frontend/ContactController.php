<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Show the public contact form.
     */
    public function showForm()
    {
        return view('frontend.contact');
    }

    /**
     * Handle contact form submission and send emails.
     */
    public function submit(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        // Send confirmation email to the user
        Mail::send('emails.contact.user', ['data' => $data], function ($message) use ($data) {
            $message->to($data['email'], $data['name'])
                ->subject('We have received your message');
        });

        // Get all Admin and Sub Admin email addresses
        $adminEmails = User::role(['Admin', 'Sub Admin'])
            ->pluck('email')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!empty($adminEmails)) {
            // Send notification email to all Admin / Sub Admin users
            Mail::send('emails.contact.admin', ['data' => $data], function ($message) use ($adminEmails, $data) {
                $message->to($adminEmails)
                    ->subject('New contact enquiry: ' . $data['subject']);
            });
        }

        return back()->with('success', 'Your message has been sent successfully.');
    }
}

