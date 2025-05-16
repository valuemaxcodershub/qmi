<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutBanks extends Model
{
    use HasFactory;
    protected $fillable = ['bank_name', 'bank_code'];
    public $timestamps = true;
}
