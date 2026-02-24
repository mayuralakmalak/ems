<?php

namespace App\Mail;

use App\Models\EventRegistration;
use App\Models\EventRegistrationPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventRegistrationPaymentSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $registration;
    public $payment;
    public $isAdmin;

    public function __construct(EventRegistration $registration, EventRegistrationPayment $payment, bool $isAdmin = false)
    {
        $this->registration = $registration;
        $this->payment = $payment;
        $this->isAdmin = $isAdmin;
    }

    public function build()
    {
        $subject = $this->isAdmin
            ? 'Event Registration Payment - ' . $this->payment->payment_number . ' (Admin)'
            : 'Payment Submitted - ' . $this->payment->payment_number;

        return $this->subject($subject)
            ->view('emails.event-registration-payment-submitted')
            ->with([
                'registration' => $this->registration,
                'payment' => $this->payment,
                'isAdmin' => $this->isAdmin,
            ]);
    }
}
