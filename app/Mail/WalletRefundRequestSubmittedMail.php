<?php

namespace App\Mail;

use App\Models\WalletRefundRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WalletRefundRequestSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $refundRequest;

    public function __construct(WalletRefundRequest $refundRequest)
    {
        $this->refundRequest = $refundRequest->load(['user', 'wallet']);
    }

    public function build()
    {
        return $this->subject('Wallet Refund Request – Special Discount #' . $this->refundRequest->id)
            ->view('emails.wallet-refund-request-submitted');
    }
}
