<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booth extends Model
{
    use HasFactory;

    protected $fillable = [
        'exhibition_id', 'name', 'category', 'booth_type', 'size_sqft',
        'sides_open', 'price', 'is_free', 'is_available', 'is_booked',
        'logo', 'coordinates', 'merged_booths', 'is_merged', 'is_split', 'parent_booth_id',
        'exhibition_booth_size_id', 'position_x', 'position_y', 'width', 'height'
    ];

    protected $casts = [
        'merged_booths' => 'array',
        'coordinates' => 'array',
        'is_free' => 'boolean',
        'is_available' => 'boolean',
        'is_booked' => 'boolean',
        'is_merged' => 'boolean',
        'is_split' => 'boolean',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function parentBooth()
    {
        return $this->belongsTo(Booth::class, 'parent_booth_id');
    }

    public function childBooths()
    {
        return $this->hasMany(Booth::class, 'parent_booth_id');
    }
}
