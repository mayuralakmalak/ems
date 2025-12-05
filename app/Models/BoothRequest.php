<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BoothRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'exhibition_id', 'user_id', 'request_type', 'booth_ids',
        'description', 'status', 'approved_by', 'approved_at',
        'rejection_reason', 'request_data'
    ];

    protected $casts = [
        'booth_ids' => 'array',
        'request_data' => 'array',
        'approved_at' => 'datetime',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function booths()
    {
        return Booth::whereIn('id', $this->booth_ids ?? [])->get();
    }
}
