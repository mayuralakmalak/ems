<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $isAdmin;

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment, bool $isAdmin = false)
    {
        $this->payment = $payment;
        $this->isAdmin = $isAdmin;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->isAdmin
            ? 'Payment Receipt - ' . $this->payment->payment_number . ' (Admin Copy)'
            : 'Payment Receipt - ' . $this->payment->payment_number;

        return $this->subject($subject)
                    ->view('emails.payment-receipt')
                    ->with([
                        'payment' => $this->payment,
                        'isAdmin' => $this->isAdmin,
                    ]);
    }
}
