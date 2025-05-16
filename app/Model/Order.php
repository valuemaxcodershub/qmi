<?php

namespace App\Model;

use App\Models\OrderDeliveryVerification;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_id', 'is_guest', 'customer_type', 'payment_status', 'order_status', 'payment_method', 'transaction_ref', 'payment_by', 'payment_date', 'verification_code',
        'payment_note', 'order_amount', 'admin_commission', 'is_pause', 'cause', 'shipping_address', 'discount_amount', 'discount_type', 'coupon_code', 'seller_id',
        'coupon_discount_bearer', 'shipping_responsibility', 'shipping_method_id', 'shipping_cost', 'is_shipping_free', 'order_group_id', 'verification_status', 'order_type',
        'seller_is', 'shipping_address_data', 'delivery_man_id', 'deliveryman_charge', 'expected_delivery_date', 'order_note', 'billing_address', 'billing_address_data',
        'extra_discount', 'extra_discount_type', 'free_delivery_bearer', 'checked', 'shipping_type', 'delivery_type', 'delivery_service_name', 'third_party_delivery_tracking_id',
    ];
    protected $casts = [
        'order_amount' => 'float',
        'discount_amount' => 'float',
        'customer_id' => 'integer',
        'shipping_address' => 'integer',
        'shipping_cost' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'billing_address'=> 'integer',
        'extra_discount'=>'float',
        'delivery_man_id'=>'integer',
        'shipping_method_id'=>'integer',
        'seller_id'=>'integer',
    ];

    public function details()
    {
        return $this->hasMany(OrderDetail::class)->orderBy('seller_id', 'ASC');
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function sellerName()
    {
        return $this->hasOne(OrderDetail::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function shipping()
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    public function shippingAddress()
    {
        return $this->belongsTo(ShippingAddress::class, 'shipping_address');
    }
    public function billingAddress()
    {
        return $this->belongsTo(ShippingAddress::class, 'billing_address');
    }

    public function delivery_man()
    {
        return $this->belongsTo(DeliveryMan::class,'delivery_man_id');
    }

    public function delivery_man_review()
    {
        return $this->hasOne(Review::class,'order_id');
    }

    public function order_transaction(){
        return $this->hasOne(OrderTransaction::class, 'order_id');
    }

    public function coupon(){
        return $this->belongsTo(Coupon::class, 'coupon_code', 'code');
    }

    public function order_status_history(){
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function order_details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    public function offline_payments(){
        return $this->belongsTo(OfflinePayments::class, 'id', 'order_id');
    }
    public function verification_images(){
        return $this->hasMany(OrderDeliveryVerification::class,'order_id');
    }
}
