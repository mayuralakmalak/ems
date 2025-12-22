<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sponsorship extends Model
{
    use HasFactory;

    protected $fillable = [
        'exhibition_id', 'name', 'description', 'deliverables', 'price', 'image', 
        'is_active', 'tier', 'max_available', 'current_count', 'display_order'
    ];

    protected $casts = [
        'deliverables' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'max_available' => 'integer',
        'current_count' => 'integer',
        'display_order' => 'integer',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function bookings()
    {
        return $this->hasMany(SponsorshipBooking::class);
    }

    public function isAvailable()
    {
        if ($this->max_available === null) {
            return true;
        }
        return $this->current_count < $this->max_available;
    }
}
