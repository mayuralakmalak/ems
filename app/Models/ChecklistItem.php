<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    protected $fillable = [
        'exhibition_id', 'name', 'description', 'is_required', 
        'due_date_days_before', 'visible_to_user', 'visible_to_admin', 'is_active'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'visible_to_user' => 'boolean',
        'visible_to_admin' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }
}
