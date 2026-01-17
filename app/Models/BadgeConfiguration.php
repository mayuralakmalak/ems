<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BadgeConfiguration extends Model
{
    protected $fillable = [
        'exhibition_id',
        'exhibition_booth_size_id',
        'badge_type',
        'quantity',
        'pricing_type',
        'price',
        'needs_admin_approval',
        'access_permissions',
    ];

    protected $casts = [
        'needs_admin_approval' => 'boolean',
        'access_permissions' => 'array',
        'price' => 'decimal:2',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function exhibitionBoothSize()
    {
        return $this->belongsTo(ExhibitionBoothSize::class, 'exhibition_booth_size_id');
    }
}
