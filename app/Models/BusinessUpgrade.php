<?php

namespace App\Models;

use App\Model\Seller;
use App\Models\SellerTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BusinessUpgrade extends Model
{
    use HasFactory;
    protected $fillable = [
        "seller_id", "contact_address", "city", "lga", "company_name", "company_email", "business_year", "company_phone", "company_address", "partner_companies", "manager_details",
        "current_seller_type", "new_seller_type", "attachments", "status", "reference"
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function sellertype()
    {
        return $this->hasOne(SellerTypes::class, 'id', 'new_seller_type');
    }

    public function currentsellertype()
    {
        return $this->hasOne(SellerTypes::class, 'id', 'current_seller_type');
    }

}
