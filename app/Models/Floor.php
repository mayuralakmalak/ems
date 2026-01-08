<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Floor extends Model
{
    use HasFactory;

    protected $fillable = [
        'exhibition_id', 'name', 'floor_number', 'description',
        'width_meters', 'height_meters', 'background_image',
        'floorplan_image', 'floorplan_images', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'floor_number' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'floorplan_images' => 'array',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function booths()
    {
        return $this->hasMany(Booth::class);
    }

    /**
     * Get the floorplan configuration JSON path for this floor
     */
    public function getFloorplanConfigPath(): string
    {
        return "floorplans/exhibition_{$this->exhibition_id}/floor_{$this->id}.json";
    }
}
