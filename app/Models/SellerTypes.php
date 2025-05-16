<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerTypes extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'amount', 'product_limit', 'rank_color', 'allowed_packages'];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
    
}
