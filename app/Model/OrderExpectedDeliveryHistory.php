<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderExpectedDeliveryHistory extends Model
{
    use HasFactory;
    protected $fillable = ['order_id', 'user_id', 'user_type', 'expected_delivery_date', 'cause'];

    protected $casts = [
        'order_id' => 'string',
        'user_id' => 'integer',
        'user_type' => 'integer',
        'expected_delivery_date' => 'date',
        'cause' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

}
