<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'thread_id', 'sender_id', 'receiver_id', 'exhibition_id', 'message',
        'is_read', 'status', 'is_closed', 'read_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_closed' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }
}
