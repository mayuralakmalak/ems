<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $isAdmin;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, bool $isAdmin = false)
    {
        $this->booking = $booking;
        $this->isAdmin = $isAdmin;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->isAdmin 
            ? 'New Booking Request - ' . $this->booking->booking_number
            : 'Booking Confirmation - ' . $this->booking->booking_number;

        return $this->subject($subject)
                    ->view('emails.booking-confirmation')
                    ->with([
                        'booking' => $this->booking,
                        'isAdmin' => $this->isAdmin,
                    ]);
    }
}
