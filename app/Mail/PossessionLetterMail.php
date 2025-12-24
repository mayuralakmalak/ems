<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PossessionLetterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $pdfPath;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, string $pdfPath)
    {
        $this->booking = $booking;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = 'Possession Letter - ' . $this->booking->booking_number . ' - ' . ($this->booking->exhibition->name ?? 'Exhibition');

        $mail = $this->subject($subject)
                    ->view('emails.possession-letter')
                    ->with([
                        'booking' => $this->booking,
                    ]);

        // Attach the PDF
        if ($this->pdfPath && \Storage::disk('public')->exists($this->pdfPath)) {
            $mail->attach(
                \Storage::disk('public')->path($this->pdfPath),
                [
                    'as' => 'Possession_Letter_' . $this->booking->booking_number . '.pdf',
                    'mime' => 'application/pdf',
                ]
            );
        }

        return $mail;
    }
}

