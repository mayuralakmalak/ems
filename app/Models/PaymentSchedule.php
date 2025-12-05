<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSchedule extends Model
{
    protected $fillable = [
        'exhibition_id',
        'part_number',
        'percentage',
        'due_date',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }
}
