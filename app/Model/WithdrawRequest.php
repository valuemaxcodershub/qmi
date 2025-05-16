<?php

namespace App\Model;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WithdrawRequest extends Model
{
    protected $fillable = [
        'user_id', 'seller_id', 'delivery_man_id', 'admin_id', 'amount', 'withdrawal_method_id', 'withdrawal_method_fields',
        'transaction_note', 'approved', 'external_reference', 'memo'
    ];
    
    protected $casts = [
        'amount' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function seller(){
        return $this->belongsTo(Seller::class,'seller_id');
    }

    public function delivery_men(){
        return $this->belongsTo(DeliveryMan::class,'delivery_man_id');
    }

    public function withdraw_method()
    {
        return $this->belongsTo(WithdrawalMethod::class,'withdrawal_method_id');
    }
}
