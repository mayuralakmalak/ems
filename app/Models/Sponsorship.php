<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sponsorship extends Model
{
    protected $fillable = [
        'exhibition_id', 'name', 'description', 'deliverables', 'price', 'image', 'is_active', 'tier'
    ];

    protected $casts = [
        'deliverables' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }
}
