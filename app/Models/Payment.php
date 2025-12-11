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
}
