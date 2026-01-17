<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'user_id', 'payment_number', 'payment_type', 'payment_method',
        'status', 'approval_status', 'amount', 'gateway_charge', 'transaction_id', 'receipt_file',
        'invoice_file', 'payment_proof', 'payment_proof_file', 'due_date', 'paid_at', 'notes', 'rejection_reason'
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'date',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this payment is for a badge
     */
    public function isBadgePayment()
    {
        return strpos($this->payment_number, 'BG') !== false;
    }

    /**
     * Get the badge associated with this payment (if it's a badge payment)
     */
    public function getBadge()
    {
        if (!$this->isBadgePayment()) {
            return null;
        }

        // Extract badge ID from payment_number: PM{timestamp}{booking_id}BG{badge_id}
        if (preg_match('/BG(\d+)/', $this->payment_number, $matches)) {
            $badgeId = (int) $matches[1];
            return \App\Models\Badge::find($badgeId);
        }

        return null;
    }

    /**
     * Check if this payment is for an additional service
     */
    public function isServicePayment()
    {
        // Check if there's an AdditionalServiceRequest that matches this payment
        // by amount and booking_id, and was approved around the same time
        if (!$this->booking_id) {
            return false;
        }

        $serviceRequest = \App\Models\AdditionalServiceRequest::where('booking_id', $this->booking_id)
            ->where('total_price', $this->amount)
            ->where('status', 'approved')
            ->whereDate('approved_at', $this->created_at->toDateString())
            ->first();

        return $serviceRequest !== null;
    }

    /**
     * Get the additional service request associated with this payment (if it's a service payment)
     */
    public function getServiceRequest()
    {
        if (!$this->isServicePayment()) {
            return null;
        }

        return \App\Models\AdditionalServiceRequest::where('booking_id', $this->booking_id)
            ->where('total_price', $this->amount)
            ->where('status', 'approved')
            ->whereDate('approved_at', $this->created_at->toDateString())
            ->with('service')
            ->first();
    }
}
