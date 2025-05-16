<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerWalletRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'amount',
        'order_id',
        'order_status'
    ];

    public $timestamps = true;

}
