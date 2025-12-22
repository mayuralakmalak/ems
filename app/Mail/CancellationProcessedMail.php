<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CancellationProcessedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = 'Booking Cancellation Processed - ' . $this->booking->booking_number;

        return $this->subject($subject)
            ->view('emails.cancellation-processed')
            ->with([
                'booking' => $this->booking,
            ]);
    }
}


