<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerifications extends Model
{
    protected $table = 'otpVerifications';

    protected $fillable = [
        'user_id',
        'email',
        'otp',
        'is_verified',
        'expire_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'expire_at' => 'datetime',
        ];
    }
}
