<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExhibitionBoothSizeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'exhibition_booth_size_id',
        'item_name',
        'quantity',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function boothSize()
    {
        return $this->belongsTo(ExhibitionBoothSize::class, 'exhibition_booth_size_id');
    }
}
