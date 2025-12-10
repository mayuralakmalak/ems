<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'exhibition_id', 'name', 'description', 'type', 'category', 
        'price', 'price_unit', 'available_from', 'available_to', 'image', 'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'available_from' => 'date',
        'available_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function bookingServices()
    {
        return $this->hasMany(BookingService::class);
    }
}
