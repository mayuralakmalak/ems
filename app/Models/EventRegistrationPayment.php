<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class EventRegistrationPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_registration_id', 'payment_number', 'payment_method', 'amount', 'gateway_charge',
        'status', 'approval_status', 'transaction_id', 'payment_proof_file', 'notes',
        'rejection_reason', 'paid_at', 'token',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_charge' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function eventRegistration()
    {
        return $this->belongsTo(EventRegistration::class);
    }

    public static function generatePaymentNumber(): string
    {
        $prefix = 'REGP';
        $count = static::count() + 1;
        return $prefix . now()->format('YmdHis') . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public static function generateToken(): string
    {
        return Str::random(48);
    }
}
