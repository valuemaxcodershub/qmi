<?php

namespace App\Models;

use App\Model\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class boosted_products extends Model
{
    use HasFactory;
    
    protected $fillable = [
        "seller_id", "product_id", "days", "price", "amount_to_bill", "reference", "expiry_date", "status"
    ];

    public $timestamps = true;
    
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
