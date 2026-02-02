<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
        'exhibition_id',
        'title',
        'code',
        'type',
        'amount',
        'status',
        'email',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }
}
