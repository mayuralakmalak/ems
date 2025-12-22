<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SponsorshipPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sponsorship_booking_id', 'user_id', 'payment_number', 'payment_method',
        'status', 'approval_status', 'amount', 'gateway_charge', 'transaction_id',
        'receipt_file', 'invoice_file', 'payment_proof', 'payment_proof_file',
        'due_date', 'paid_at', 'notes', 'rejection_reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_charge' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function sponsorshipBooking()
    {
        return $this->belongsTo(SponsorshipBooking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

