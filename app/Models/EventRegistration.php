<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class EventRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'exhibition_id', 'booking_id', 'type', 'registration_number', 'first_name', 'last_name', 'email', 'phone',
        'id_proof_file', 'company', 'designation', 'city', 'state', 'country',
        'fee_amount', 'fee_tier', 'paid_amount', 'approval_status', 'approved_by', 'approved_at',
        'rejection_reason', 'payment_status', 'token',
    ];

    protected $casts = [
        'fee_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payments()
    {
        return $this->hasMany(EventRegistrationPayment::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getOutstandingAmountAttribute()
    {
        return $this->fee_amount - $this->paid_amount;
    }

    public function isFullyPaid(): bool
    {
        return $this->fee_amount <= 0 || $this->paid_amount >= $this->fee_amount;
    }

    public static function generateRegistrationNumber(): string
    {
        $prefix = 'REG';
        $count = static::count() + 1;
        return $prefix . now()->format('Ymd') . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public static function generateToken(): string
    {
        return Str::random(48);
    }
}
