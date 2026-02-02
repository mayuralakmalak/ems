<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CmsPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'show_in_footer',
        'show_in_header',
        'is_active',
    ];

    protected $casts = [
        'show_in_footer' => 'boolean',
        'show_in_header' => 'boolean',
        'is_active' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeShowInFooter($query)
    {
        return $query->where('show_in_footer', true);
    }

    public function scopeShowInHeader($query)
    {
        return $query->where('show_in_header', true);
    }
}
