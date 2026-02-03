<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentDueIn3DaysMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $paymentNumber;

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment, int $paymentNumber)
    {
        $this->payment = $payment;
        $this->paymentNumber = $paymentNumber;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $numberSuffix = $this->getNumberSuffix($this->paymentNumber);
        $subject = 'Reminder: Your ' . $this->paymentNumber . $numberSuffix . ' payment is due in 3 days - ' . $this->payment->payment_number;

        return $this->subject($subject)
                    ->view('emails.payment-due-reminder-3days')
                    ->with([
                        'payment' => $this->payment,
                        'paymentNumber' => $this->paymentNumber,
                        'numberSuffix' => $numberSuffix,
                    ]);
    }

    /**
     * Get the suffix for the payment number (st, nd, rd, th)
     */
    private function getNumberSuffix(int $number): string
    {
        $lastDigit = $number % 10;
        $lastTwoDigits = $number % 100;

        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 13) {
            return 'th';
        }

        return match ($lastDigit) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
    }
}
