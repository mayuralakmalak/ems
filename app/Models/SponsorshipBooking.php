<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SponsorshipBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'sponsorship_id', 'booking_id', 'user_id', 'exhibition_id', 
        'booking_number', 'amount', 'paid_amount', 'status', 'payment_status',
        'contact_emails', 'contact_numbers', 'logo', 'notes',
        'approval_status', 'approved_by', 'approved_at', 'rejection_reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'contact_emails' => 'array',
        'contact_numbers' => 'array',
        'approved_at' => 'datetime',
    ];

    public function sponsorship()
    {
        return $this->belongsTo(Sponsorship::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function payments()
    {
        return $this->hasMany(SponsorshipPayment::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getOutstandingAmountAttribute()
    {
        return $this->amount - $this->paid_amount;
    }

    public function isFullyPaid()
    {
        return $this->paid_amount >= $this->amount;
    }
}
