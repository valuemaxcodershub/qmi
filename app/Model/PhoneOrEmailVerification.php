<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PhoneOrEmailVerification extends Model
{
    
    protected $fillable = [
        'phone_or_email', 'token', 'otp_hit_count', 'is_temp_blocked', 'temp_block_time', 'user_type', 'use_case', 'expires_at'
    ];

}
