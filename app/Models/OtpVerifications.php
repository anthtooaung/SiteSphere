<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerifications extends Model
{
    protected $table = 'otpVerifications';

    protected $fillable = [
        'user_id',
        'otp',
        'is_verified',
        'expire_at',
    ];
}
