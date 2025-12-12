<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'status',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class, 'type', 'slug');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }
}
