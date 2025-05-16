<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpToken extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'type', 'token', 'status', 'use_case'];
    public $timestamps = true;
}
