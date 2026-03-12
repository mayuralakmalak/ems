<?php

namespace App\Mail;

use App\Models\WalletRefundRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WalletRefundApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $refundRequest;
    public $isAdmin;

    public function __construct(WalletRefundRequest $refundRequest, bool $isAdmin = false)
    {
        $this->refundRequest = $refundRequest->load(['user', 'wallet', 'processor']);
        $this->isAdmin = $isAdmin;
    }

    public function build()
    {
        $subject = $this->isAdmin
            ? 'Wallet Refund Approved (Admin) – Request #' . $this->refundRequest->id
            : 'Your Wallet Refund Request Has Been Approved';

        return $this->subject($subject)
            ->view('emails.wallet-refund-approved')
            ->with([
                'refundRequest' => $this->refundRequest,
                'isAdmin' => $this->isAdmin,
            ]);
    }
}
