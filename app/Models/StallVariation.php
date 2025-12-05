<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StallVariation extends Model
{
    protected $fillable = [
        'exhibition_id',
        'stall_type',
        'sides_open',
        'front_view',
        'side_view_left',
        'side_view_right',
        'back_view',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }
}
