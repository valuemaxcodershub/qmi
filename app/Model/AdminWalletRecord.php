<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminWalletRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'amount',
        'order_id',
        'type',
        'order_status'
    ];

    public $timestamps = true;

}
