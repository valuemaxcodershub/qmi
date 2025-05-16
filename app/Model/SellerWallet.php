<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SellerWallet extends Model
{
    protected $casts = [
        'total_earning' => 'float',
        'withdrawn' => 'float',
        'commission_given' => 'float',
        'pending_withdraw' => 'float',
        'delivery_charge_earned' => 'float',
        'collected_cash' => 'float',
        'loyalty_wallet' => 'float',
        'total_tax_collected' => 'float',
        'escrow_balance' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = [
        'total_earning', 'withdrawn', 'commission_given', 'pending_withdraw', 'delivery_charge_earned', 'collected_cash', 'loyalty_wallet',
        'escrow_balance', 'total_tax_collected', 'seller_id',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
