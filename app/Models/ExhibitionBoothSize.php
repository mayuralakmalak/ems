<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExhibitionBoothSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'exhibition_id',
        'size_sqft',
        'row_price',
        'orphan_price',
        'category',
        'images',
    ];

    protected $casts = [
        'size_sqft' => 'float',
        'row_price' => 'float',
        'orphan_price' => 'float',
        'images' => 'array',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function items()
    {
        return $this->hasMany(ExhibitionBoothSizeItem::class);
    }
}
