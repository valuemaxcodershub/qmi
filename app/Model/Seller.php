<?php

namespace App\Model;

use App\Models\Competition;
use App\Models\SellerTypes;
use App\Models\CompetitionJoined;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Seller extends Authenticatable
{
    use Notifiable;

    protected $casts = [
        'id' => 'integer',
        'orders_count' => 'integer',
        'product_count' => 'integer',
        'pos+status' => 'integer'
    ];

    protected $hidden = [
        'password', 'remember_token'  
    ];  
    
    public function getFullnameAttribute()
    {
        return $this->f_name . ' ' . $this->l_name;
    }

    public function scopeApproved($query)
    {
        return $query->where(['status'=>'approved']);
    }

    public function shop()
    {
        return $this->hasOne(Shop::class, 'seller_id', 'id');
    }

    public function shops()
    {
        return $this->hasMany(Shop::class, 'seller_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    public function product()
    {
        return $this->hasMany(Product::class, 'user_id')->where(['added_by'=>'seller']);
    }

    public function wallet()
    {
        return $this->hasOne(SellerWallet::class);
    }

    public function sellertype()
    {
        return $this->hasOne(SellerTypes::class, 'id', 'seller_type');
    }

    public function coupon(){
        return $this->hasMany(Coupon::class, 'seller_id')
            ->where(['coupon_bearer'=>'seller', 'status'=>1])
            ->whereDate('start_date','<=',date('Y-m-d'))
            ->whereDate('expire_date','>=',date('Y-m-d'));
    }

    public function competitions()
    {
        return $this->belongsToMany(Competition::class, 'competition_joined', 'seller_id', 'competition_id');
    }

    public function competitionjoined()
{
    return $this->hasMany(CompetitionJoined::class, 'seller_id');
}

}
