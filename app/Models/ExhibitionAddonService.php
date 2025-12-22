<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExhibitionAddonService extends Model
{
    use HasFactory;

    protected $fillable = [
        'exhibition_id',
        'item_name',
        'price_per_quantity',
        'cutoff_date',
    ];

    protected $casts = [
        'price_per_quantity' => 'float',
        'cutoff_date' => 'date',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }
}
