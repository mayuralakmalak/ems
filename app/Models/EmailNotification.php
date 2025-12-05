<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailNotification extends Model
{
    protected $fillable = [
        'event_type', 'subject_line', 'email_body', 'recipients', 'category', 'is_enabled'
    ];

    protected $casts = [
        'recipients' => 'array',
        'is_enabled' => 'boolean',
    ];
}
