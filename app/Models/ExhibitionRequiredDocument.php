<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExhibitionRequiredDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'exhibition_id',
        'document_name',
        'document_type',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'required_document_id');
    }
}
