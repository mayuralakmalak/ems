<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StallScheme extends Model
{
    protected $fillable = [
        'exhibition_id',
        'size_sqm',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }
}
