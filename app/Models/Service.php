<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'exhibition_id', 'name', 'description', 'type', 'price', 'image', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }
}
