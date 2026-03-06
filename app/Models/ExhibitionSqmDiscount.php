<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExhibitionSqmDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'exhibition_id',
        'sqm',
        'operator',
        'percentage',
        'sort_order',
    ];

    protected $casts = [
        'sqm' => 'decimal:2',
        'percentage' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }
}

