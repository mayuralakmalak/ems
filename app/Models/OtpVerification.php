<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    protected $fillable = [
        'phone',
        'email',
        'otp',
        'type',
        'is_verified',
        'expires_at',
    ];
}
