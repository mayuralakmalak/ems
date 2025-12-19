<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'user_id', 'required_document_id', 'name', 'type', 'file_path', 'file_size',
        'status', 'rejection_reason', 'expiry_date', 'version'
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(DocumentCategory::class, 'type', 'slug');
    }

    public function requiredDocument()
    {
        return $this->belongsTo(ExhibitionRequiredDocument::class, 'required_document_id');
    }

    public function canBeEdited()
    {
        return $this->status !== 'approved';
    }

}
