<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SponsorshipBooking extends Model
{
    protected $fillable = [
        'sponsorship_id', 'booking_id', 'user_id', 'exhibition_id', 'amount', 'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
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
}
