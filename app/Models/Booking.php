<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'exhibition_id',
        'user_id',
        'booth_id',
        'selected_booth_ids',
        'included_item_extras',
        'booking_number',
        'status',
        'total_amount',
        'paid_amount',
        'discount_percent',
        'contact_emails',
        'contact_numbers',
        'logo',
        'possession_letter_issued',
        'cancellation_reason',
        'cancellation_type',
        'cancellation_amount',
        'account_details',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'selected_booth_ids' => 'array',
        'included_item_extras' => 'array',
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

    public function additionalServiceRequests()
    {
        return $this->hasMany(AdditionalServiceRequest::class);
    }

    /**
     * Check if all payments for this booking are completed
     */
    public function isFullyPaid()
    {
        // Check if paid_amount is greater than or equal to total_amount
        // Allow small floating point differences (0.01)
        return $this->paid_amount >= ($this->total_amount - 0.01);
    }

    /**
     * Check if all payment installments are approved/completed
     */
    public function areAllPaymentsCompleted()
    {
        $payments = $this->payments;
        
        if ($payments->isEmpty()) {
            return false;
        }

        // Check if all payments are completed and approved
        foreach ($payments as $payment) {
            if ($payment->status !== 'completed' || $payment->approval_status !== 'approved') {
                return false;
            }
        }

        return true;
    }
}
