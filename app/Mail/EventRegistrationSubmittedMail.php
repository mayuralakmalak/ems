<?php

namespace App\Mail;

use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventRegistrationSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $registration;
    public $isAdmin;

    public function __construct(EventRegistration $registration, bool $isAdmin = false)
    {
        $this->registration = $registration;
        $this->isAdmin = $isAdmin;
    }

    public function build()
    {
        $subject = $this->isAdmin
            ? 'New Event Registration - ' . $this->registration->registration_number . ' (' . ucfirst($this->registration->type) . ')'
            : 'Registration Submitted - ' . $this->registration->registration_number;

        return $this->subject($subject)
            ->view('emails.event-registration-submitted')
            ->with([
                'registration' => $this->registration,
                'isAdmin' => $this->isAdmin,
            ]);
    }
}
