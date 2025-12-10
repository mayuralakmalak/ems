<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'exhibition_id', 'user_id', 'booth_id', 'selected_booth_ids', 'booking_number', 'status',
        'total_amount', 'paid_amount', 'discount_percent',
        'contact_emails', 'contact_numbers', 'logo',
        'possession_letter_issued', 'cancellation_reason',
        'cancellation_type', 'cancellation_amount', 'account_details',
        'approval_status', 'approved_by', 'approved_at', 'rejection_reason'
    ];

    protected $casts = [
        'selected_booth_ids' => 'array',
        'contact_emails' => 'array',
        'contact_numbers' => 'array',
        'possession_letter_issued' => 'boolean',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function booth()
    {
        return $this->belongsTo(Booth::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function badges()
    {
        return $this->hasMany(Badge::class);
    }

    public function bookingServices()
    {
        return $this->hasMany(BookingService::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
