<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\Exhibition;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserBookingPaymentSummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var User */
    public $user;

    /** @var \Illuminate\Support\Collection<int, Booking> */
    public $bookings;

    /** @var Exhibition|null */
    public $exhibition;

    /**
     * Create a new message instance.
     *
     * @param  \Illuminate\Support\Collection<int, Booking>  $bookings
     */
    public function __construct(User $user, $bookings, ?Exhibition $exhibition = null)
    {
        $this->user = $user;
        $this->bookings = $bookings;
        $this->exhibition = $exhibition;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = 'Your Booking & Payment Summary';
        if ($this->exhibition) {
            $subject .= ' - ' . $this->exhibition->name;
        }
        $subject .= ' - ' . config('app.name');

        return $this->subject($subject)
            ->view('emails.user-booking-payment-summary');
    }
}
