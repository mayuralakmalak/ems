<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'user_id', 'exhibition_id', 'badge_type', 'name',
        'email', 'phone', 'photo', 'qr_code', 'status', 'is_paid',
        'price', 'access_permissions', 'valid_for_date', 'is_scanned', 'scanned_at'
    ];

    protected $casts = [
        'access_permissions' => 'array',
        'valid_for_date' => 'date',
        'is_paid' => 'boolean',
        'is_scanned' => 'boolean',
        'scanned_at' => 'datetime',
    ];

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
