<?php

namespace App\CPU;

use App\User;
use Exception;
use Carbon\Carbon;
use App\Model\Cart;
use App\Model\Shop;
use App\Model\Admin;
use App\Model\Color;
use App\Model\Order;
use App\Model\Coupon;
use App\Model\Seller;
use App\Model\Product;
use App\Model\AdminWallet;
use App\Model\OrderDetail;
use App\Model\Transaction;
use App\Model\CartShipping;
use App\Model\SellerWallet;
use App\Model\ShippingType;
use App\Traits\CommonTrait;
use Illuminate\Support\Str;
use App\Model\BusinessSetting;
use App\Model\OfflinePayments;
use App\Model\ShippingAddress;
use App\Model\OrderTransaction;
use App\Model\AdminWalletRecord;
use App\Model\SellerWalletRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;


class OrderManager
{
    use CommonTrait;

    public static function track_order($order_id)
    {
        $order = Order::with(['delivery_man', 'order_status_history' => function ($query) {
            return $query->latest();
        }])->where(['id' => $order_id])->first();
        $order['billing_address_data'] = json_decode($order['billing_address_data']);
        $order['shipping_address_data'] = json_decode($order['shipping_address_data']);
        return $order;
    }

    public static function gen_unique_id()
    {
        return rand(1000, 9999) . '-' . Str::random(5) . '-' . time();
    }

    public static function order_summary($order)
    {
        $sub_total = 0;
        $total_tax = 0;
        $total_discount_on_product = 0;
        foreach ($order->details as $key => $detail) {
            $sub_total += $detail->price * $detail->qty;
            $total_tax += $detail->tax;
            $total_discount_on_product += $detail->discount;
        }
        $total_shipping_cost = $order['shipping_cost'];
        return [
            'subtotal' => $sub_total,
            'total_tax' => $total_tax,
            'total_discount_on_product' => $total_discount_on_product,
            'total_shipping_cost' => $total_shipping_cost,
        ];
    }

    public static function order_summary_before_place_order($cart, $coupon_discount)
    {
        $coupon_code = session()->has('coupon_code') ? session('coupon_code') : 0;
        $coupon = Coupon::where(['code' => $coupon_code])
            ->where('status', 1)
            ->first();

        $sub_total = 0;
        $total_discount_on_product = 0;

        if ($coupon && ($coupon->seller_id == NULL || $coupon->seller_id == '0' || $coupon->seller_id == $cart[0]->seller_id)) {
            $coupon_discount = $coupon->coupon_type == 'free_delivery' ? 0 : $coupon_discount;
        } else {
            $coupon_discount = 0;
        }

        foreach ($cart as $item) {
            $sub_total += $item->price * $item->quantity;
            $total_discount_on_product += $item->discount * $item->quantity;
        }

        $order_total = $sub_total - $total_discount_on_product - $coupon_discount;
        return [
            'order_total' => $order_total
        ];
    }

    public static function stock_update_on_order_status_change($order, $status)
    {
        if ($status == 'returned' || $status == 'failed' || $status == 'canceled') {
            foreach ($order->details as $detail) {
                if ($detail['is_stock_decreased'] == 1) {
                    $product = Product::find($detail['product_id']);
                    $type = $detail['variant'];
                    $var_store = [];
                    foreach (json_decode($product['variation'], true) as $var) {
                        if ($type == $var['type']) {
                            $var['qty'] += $detail['qty'];
                        }
                        array_push($var_store, $var);
                    }
                    Product::where(['id' => $product['id']])->update([
                        'variation' => json_encode($var_store),
                        'current_stock' => $product['current_stock'] + $detail['qty'],
                    ]);
                    OrderDetail::where(['id' => $detail['id']])->update([
                        'is_stock_decreased' => 0,
                        'delivery_status' => $status
                    ]);
                }
            }
        } else {
            foreach ($order->details as $detail) {
                if ($detail['is_stock_decreased'] == 0) {
                    $product = Product::find($detail['product_id']);

                    $type = $detail['variant'];
                    $var_store = [];
                    foreach (json_decode($product['variation'], true) as $var) {
                        if ($type == $var['type']) {
                            $var['qty'] -= $detail['qty'];
                        }
                        array_push($var_store, $var);
                    }
                    Product::where(['id' => $product['id']])->update([
                        'variation' => json_encode($var_store),
                        'current_stock' => $product['current_stock'] - $detail['qty'],
                    ]);
                    OrderDetail::where(['id' => $detail['id']])->update([
                        'is_stock_decreased' => 1,
                        'delivery_status' => $status
                    ]);
                }
            }
        }
    }

    public static function wallet_manage_on_order_status_change($order, $received_by)
    {
        $order = Order::find($order['id']);
        $order_summary = OrderManager::order_summary($order);
        $order_amount = $order_summary['subtotal'] - $order_summary['total_discount_on_product'] - $order['discount_amount'];
        $commission = $order['admin_commission'];
        $shipping_model = $order->shipping_responsibility;

        if (AdminWallet::where('admin_id', 1)->first() == false) {
            DB::table('admin_wallets')->insert([
                'admin_id' => 1,
                'withdrawn' => 0,
                'commission_earned' => 0,
                'inhouse_earning' => 0,
                'delivery_charge_earned' => 0,
                'pending_amount' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        if (SellerWallet::where('seller_id', $order['seller_id'])->first() == false) {
            DB::table('seller_wallets')->insert([
                'seller_id' => $order['seller_id'],
                'withdrawn' => 0,
                'commission_given' => 0,
                'total_earning' => 0,
                'pending_withdraw' => 0,
                'delivery_charge_earned' => 0,
                'collected_cash' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // coupon transaction start
        if ($order->coupon_code && $order->coupon_code != '0' && $order->seller_is == 'seller' && $order->discount_type == 'coupon_discount') {
            if ($order->coupon_discount_bearer == 'inhouse') {
                $seller_wallet = SellerWallet::where('seller_id', $order->seller_id)->first();
                $seller_wallet->total_earning += $order->discount_amount;
                $seller_wallet->save();

                $paid_by = 'admin';
                $payer_id = 1;
                $payment_receiver_id = $order->seller_id;
                $paid_to = 'seller';

            } elseif ($order->coupon_discount_bearer == 'seller') {
                $paid_by = 'seller';
                $payer_id = $order->seller_id;
                $payment_receiver_id = $order->seller_id;
                $paid_to = 'admin';
            }

            $transaction = new Transaction();
            $transaction->order_id = $order->id;
            $transaction->payment_for = 'coupon_discount';
            $transaction->payer_id = $payer_id;
            $transaction->payment_receiver_id = $payment_receiver_id;
            $transaction->paid_by = $paid_by;
            $transaction->paid_to = $paid_to;
            $transaction->payment_status = 'disburse';
            $transaction->amount = $order->discount_amount;
            $transaction->transaction_type = 'expense';
            $transaction->save();
        }
        // coupon transaction end

        // free delivery over amount transaction start
        if($order->is_shipping_free && $order->seller_is == 'seller') {

            $seller_wallet = SellerWallet::where('seller_id', $order->seller_id)->first();
            $admin_wallet = AdminWallet::where('admin_id', 1)->first();

            if ($order->free_delivery_bearer == 'admin' && $order->shipping_responsibility == 'sellerwise_shipping') {
                $seller_wallet->delivery_charge_earned += $order->extra_discount;
                $seller_wallet->total_earning += $order->extra_discount;

                $admin_wallet->delivery_charge_earned -= $order->extra_discount;
                $admin_wallet->inhouse_earning -= $order->extra_discount;

                $paid_by = 'admin';
                $payer_id = 1;
                $payment_receiver_id = $order->seller_id;
                $paid_to = 'seller';

            } elseif ($order->free_delivery_bearer == 'seller' && $order->shipping_responsibility == 'inhouse_shipping') {
                $seller_wallet->delivery_charge_earned -= $order->extra_discount;
                $seller_wallet->total_earning -= $order->extra_discount;

                $admin_wallet->delivery_charge_earned += $order->extra_discount;
                $admin_wallet->inhouse_earning += $order->extra_discount;

                $paid_by = 'seller';
                $payer_id = $order->seller_id;
                $payment_receiver_id = $order->seller_id;
                $paid_to = 'admin';
            }

            $seller_wallet->save();
            $admin_wallet->save();

            $transaction = new Transaction();
            $transaction->order_id = $order->id;
            $transaction->payment_for = 'free_shipping_over_order_amount';
            $transaction->payer_id = $payer_id;
            $transaction->payment_receiver_id = $payment_receiver_id;
            $transaction->paid_by = $paid_by;
            $transaction->paid_to = $paid_to;
            $transaction->payment_status = 'disburse';
            $transaction->amount = $order->extra_discount;
            $transaction->transaction_type = 'expense';
            $transaction->save();
        }
        // free delivery over amount transaction end



        if ($order['payment_method'] == 'cash_on_delivery' || $order['payment_method'] == 'offline_payment') {
            DB::table('order_transactions')->insert([
                'transaction_id' => OrderManager::gen_unique_id(),
                'customer_id' => $order['customer_id'],
                'seller_id' => $order['seller_id'],
                'seller_is' => $order['seller_is'],
                'order_id' => $order['id'],
                'order_amount' => $order_amount,
                'seller_amount' => $order_amount - $commission,
                'admin_commission' => $commission,
                'received_by' => $received_by,
                'status' => 'disburse',
                'delivery_charge' => $order['shipping_cost'] - ($order['is_shipping_free'] ? $order['extra_discount']:0),
                'tax' => $order_summary['total_tax'],
                'delivered_by' => $received_by,
                'payment_method' => $order['payment_method'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $wallet = AdminWallet::where('admin_id', 1)->first();
            $wallet->commission_earned += $commission;
            ($shipping_model == 'inhouse_shipping' && !$order['is_shipping_free']) ? $wallet->delivery_charge_earned += $order['shipping_cost'] : null;
            $wallet->save();

            if ($order['seller_is'] == 'admin') {
                $wallet = AdminWallet::where('admin_id', 1)->first();
                $wallet->inhouse_earning += $order_amount;
                ($shipping_model == 'sellerwise_shipping' && !$order['is_shipping_free']) ? $wallet->delivery_charge_earned += $order['shipping_cost'] : null;

                $wallet->total_tax_collected += $order_summary['total_tax'];
                $wallet->save();
            } else {
                $wallet = SellerWallet::where('seller_id', $order['seller_id'])->first();
                $wallet->commission_given += $commission;
                $wallet->total_tax_collected += $order_summary['total_tax'];

                if ($shipping_model == 'sellerwise_shipping') {
                    !$order['is_shipping_free'] ? $wallet->delivery_charge_earned += $order['shipping_cost'] : null;
                    $wallet->collected_cash += $order['order_amount']; //total order amount
                } else {
                    $wallet->total_earning += ($order_amount - $commission) + $order_summary['total_tax'];
                }

                $wallet->save();
            }
        } else {
            $transaction = OrderTransaction::where(['order_id' => $order['id']])->first();
            $transaction->status = 'disburse';
            $transaction->save();

            $wallet = AdminWallet::where('admin_id', 1)->first();
            $wallet->commission_earned += $commission;
            $wallet->pending_amount -= $order['order_amount'];
            ($shipping_model == 'inhouse_shipping' && !$order['is_shipping_free']) ? $wallet->delivery_charge_earned += $order['shipping_cost'] : null;
            $wallet->save();

            if ($order['seller_is'] == 'admin') {
                $wallet = AdminWallet::where('admin_id', 1)->first();
                $wallet->inhouse_earning += $order_amount;
                ($shipping_model == 'sellerwise_shipping' && !$order['is_shipping_free']) ? $wallet->delivery_charge_earned += $order['shipping_cost']:null;
                $wallet->total_tax_collected += $order_summary['total_tax'];
                $wallet->save();
            } else {
                $wallet = SellerWallet::where('seller_id', $order['seller_id'])->first();
                $wallet->commission_given += $commission;

                if ($shipping_model == 'sellerwise_shipping') {
                   !$order['is_shipping_free'] ? $wallet->delivery_charge_earned += $order['shipping_cost'] : null;
                    $wallet->total_earning += ($order_amount - $commission) + $order_summary['total_tax'] + $order['shipping_cost'];
                } else {
                    $wallet->total_earning += ($order_amount - $commission) + $order_summary['total_tax'];
                }

                $wallet->total_tax_collected += $order_summary['total_tax'];
                $wallet->save();
            }
        }
    }

    public static function coupon_process($data, $coupon)
    {
        $req = array_key_exists('request', $data) ? $data['request'] : null;
        $coupon_discount = 0;
        if (session()->has('coupon_discount')) {
            $coupon_discount = session('coupon_discount');
        } elseif ($req['coupon_discount']) {
            $coupon_discount = $req['coupon_discount'];
        }

        $carts = $req ? CartManager::get_cart_for_api($req) : CartManager::get_cart();
        $group_id_wise_cart = CartManager::get_cart($data['cart_group_id']);
        $total_amount = 0;
        foreach ($carts as $cart) {
            if (($coupon->seller_id == NULL && $cart->seller_is == 'admin') || $coupon->seller_id == '0' || ($coupon->seller_id == $cart->seller_id && $cart->seller_is == 'seller')) {
                $total_amount += ($cart['price'] * $cart['quantity']);

            }
        }

        if (($group_id_wise_cart[0]->seller_is == 'admin' && $coupon->seller_id == NULL) || $coupon->seller_id == '0' || ($coupon->seller_id == $group_id_wise_cart[0]->seller_id && $group_id_wise_cart[0]->seller_is == 'seller')) {
            $cart_group_ids = CartManager::get_cart_group_ids($req ?? null);
            $discount = 0;

            if ($coupon->coupon_type == 'discount_on_purchase' || $coupon->coupon_type == 'first_order') {
                $group_id_percent = array();
                foreach ($cart_group_ids as $cart_group_id) {
                    $cart_group_data = $req ? CartManager::get_cart_for_api($req, $cart_group_id) : CartManager::get_cart($cart_group_id);
                    $cart_group_amount = 0;
                    if ($coupon->seller_id == NULL || $coupon->seller_id == '0' || $coupon->seller_id == $cart_group_data[0]->seller_id) {
                        $cart_group_amount = $cart_group_data->sum(function ($item) {
                            return ($item['price'] * $item['quantity']);
                        });
                    }
                    $percent = number_format(($cart_group_amount / $total_amount) * 100, 2);
                    $group_id_percent[$cart_group_id] = $percent;
                }
                $discount = ($group_id_percent[$data['cart_group_id']] * $coupon_discount) / 100;

            } elseif ($coupon->coupon_type == 'free_delivery') {
                $shippingMethod = Helpers::get_business_settings('shipping_method');

                $free_shipping_by_group_id = array();
                foreach ($cart_group_ids as $cart_group_id) {
                    $cart_group_data = $req ? CartManager::get_cart_for_api($req, $cart_group_id) : CartManager::get_cart($cart_group_id);

                    if ($shippingMethod == 'inhouse_shipping') {
                        $admin_shipping = \App\Model\ShippingType::where('seller_id', 0)->first();
                        $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
                    } else {

                        if ($cart_group_data[0]->seller_is == 'admin') {
                            $admin_shipping = \App\Model\ShippingType::where('seller_id', 0)->first();
                            $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
                        } else {
                            $seller_shipping = \App\Model\ShippingType::where('seller_id', $cart_group_data[0]->seller_id)->first();
                            $shipping_type = isset($seller_shipping) == true ? $seller_shipping->shipping_type : 'order_wise';
                        }
                    }

                    if ($shipping_type == 'order_wise' && (($coupon->seller_id == null && $cart_group_data[0]->seller_is == 'admin') || $coupon->seller_id == '0' || $coupon->seller_id == $cart_group_data[0]->seller_id)) {
                        $free_shipping_by_group_id[$cart_group_id] = $cart_group_data[0]->cart_shipping->shipping_cost ?? 0;
                    } else {
                        if (($coupon->seller_id == null && $cart_group_data[0]->seller_is == 'admin') || $coupon->seller_id == '0' || $coupon->seller_id == $cart_group_data[0]->seller_id) {
                            $shipping_cost = CartManager::get_shipping_cost($data['cart_group_id']);
                            $free_shipping_by_group_id[$cart_group_id] = $shipping_cost;
                        }
                    }
                }
                $discount = (isset($free_shipping_by_group_id[$data['cart_group_id']]) && $free_shipping_by_group_id[$data['cart_group_id']]) ? $free_shipping_by_group_id[$data['cart_group_id']] : 0;
            }
            $calculate_data = array(
                'discount' => $discount,
                'coupon_bearer' => $coupon->coupon_bearer,
                'coupon_code' => $coupon->code,
                'coupon_type' => $coupon->coupon_type,
            );
            return $calculate_data;
        }

        $calculate_data = array(
            'discount' => 0,
            'coupon_bearer' => 'inhouse',
            'coupon_code' => 0,
            'coupon_type' => NULL,
        );

        return $calculate_data;
    }
    
    // Used in giving sellers, admin and pavi their commission
    public static function split_seller_admin_fund($orderId) {
        $getCart = Cart::where (['customer_id' => auth()->guard('customer')->user()->id])->get();
        if ($getCart !== null) {
            $totalAmountPaid = $totalDeliveryFee = 0;
            
            // Get the commission percent set by PAVI NG
            $retrieveCommission = BusinessSetting::where('type', 'sales_commission')->first();
            $saleCommision = $retrieveCommission == null ? 0 : $retrieveCommission->value;
    
            foreach($getCart as $cartIndex => $cartData) {
                $price = ($cartData['price'] - $cartData['discount']) * $cartData['quantity'];
                $sellerId = $cartData['seller_id'];
    
                // Total amount paid after deducting discount
                $totalAmountPaid +=  $price;
    
                // How much is seller getting per sales of each item ?
                $sellerEscrowAmount = $price - (($price * $saleCommision)/100);
                
                // Total delivery fee...
                $totalDeliveryFee += $cartData['shipping_cost'];
    
                // Is seller an Admin or normal seller ?
                $isSellerAdmin = $cartData['seller_is'] == "admin" ? true : false;
    
                if ($isSellerAdmin) {
                    $createInhouseWallet = AdminWalletRecord::create([
                        'admin_id' => 1,
                        'amount' => (double) $sellerEscrowAmount,
                        'type' => 'sales',
                        'order_status' => 'pending'
                    ]);
                    
                    if ($createInhouseWallet) {
                        $adminWallet = AdminWallet::where('admin_id', 1)->first();
                        $adminWallet->pending_amount = (double) $adminWallet->pending_amount + $sellerEscrowAmount;
                        $adminWallet->save();
                    }
                } else {
                    $sellerWallet = SellerWallet::where(['seller_id' => $sellerId])->first();
    
                    if ($sellerWallet) {
                        $sellerWallet->escrow_balance = (double) $sellerWallet->escrow_balance + $sellerEscrowAmount;
                        $sellerWallet->save();
        
                        SellerWalletRecord::create([
                            'seller_id' => 1,
                            'order_id' => $orderId,
                            'amount' => (double) $sellerEscrowAmount,
                            'order_status' => 'pending'
                        ]);
                    }
                }            
            }
    
            // How much is PAVI getting from the sales aside delivery fee...???
            $paviCommission = (($totalAmountPaid * $saleCommision)/100);
    
            $adminWallet = AdminWallet::where('admin_id', 1)->first();
            $adminWallet->commission_escrow = (double) $adminWallet->commission_escrow + $paviCommission;
            $adminWallet->delivery_charge_escrow = (double) $adminWallet->delivery_charge_escrow + $totalDeliveryFee;
            if ($adminWallet->save()) {
                AdminWalletRecord::create([
                    'admin_id' => 1,
                    'order_id' => $orderId,
                    'amount' => (double) $totalDeliveryFee,
                    'type' => 'delivery_fee',
                    'order_status' => 'pending'
                ]);
                
                AdminWalletRecord::create([
                    'admin_id' => 1,
                    'order_id' => $orderId,
                    'amount' => (double) $paviCommission,
                    'type' => 'commission',
                    'order_status' => 'pending'
                ]);
            }
        }
    }

    public static function distribute_delivered_order_funds($orderIdSelected) : bool {
        if (is_array($orderIdSelected)) {
            $orderDetailQuery = OrderDetail::whereIn('id', $orderIdSelected)->whereIn('delivery_status', ['pending', 'confirmed', 'out_for_delivery']);
        } else {
            $orderDetailQuery = OrderDetail::whereJsonContains('order_info->reference', $orderIdSelected)
                                        ->whereIn('delivery_status', ['pending', 'confirmed', 'out_for_delivery']);
        }
        $orderDetails = $orderDetailQuery->get();
        $orderReference = null;

        try {
            $transactionSuccess = true; // Flag to track transaction success
            DB::transaction(function () use ($orderDetails, &$orderReference, &$transactionSuccess) {
                foreach ($orderDetails as $detailKey => $detail) {
                    $orderInfo = json_decode($detail->order_info, true);
                    $orderReference = $orderInfo['reference'];
                    $productDetail = json_decode($detail->product_details, true);
                    $detailVariant = $detail->variant;
                    $quantityBuy = (int) $detail->qty;
                    $adminCommissionOnSales = $orderInfo['commission_percentage'];
                    $deliveryFee = (float) $productDetail['shipping_cost'];
                    $unitPrice = 0;
                    $variation = $productDetail['variation'];

                    if (!empty($detailVariant)) {
                        $decodeVariation = json_decode($variation, true);
                        foreach ($decodeVariation as $variationIndex => $variation) {
                            if ($variation['type'] == $detailVariant) {
                                $unitPrice = $variation['price'];
                                break;
                            } 
                        }
                    } else {
                        $unitPrice = $productDetail['unit_price'];
                    }
                    $amountPaidPerItem = (float) $unitPrice * $quantityBuy;
                    $adminCommission = (float) round(($adminCommissionOnSales / 100 * $amountPaidPerItem), 2);
                    $sellerFee = (double) round(($amountPaidPerItem - $adminCommission), 2);

                    // Is seller an Admin or normal seller ?
                    $isSellerAdmin = $productDetail['added_by'] == "admin";

                    // Let's move delivery charge and commission from admin escrow to main wallet...
                    $adminWallet = AdminWallet::where('admin_id', 1)->first();
                    $adminWallet->delivery_charge_escrow -= $deliveryFee;
                    $adminWallet->delivery_charge_earned += $deliveryFee;
                    $adminWallet->commission_escrow -= $adminCommission;
                    $adminWallet->commission_earned += $adminCommission;
                    if ($isSellerAdmin) {
                        $adminWallet->pending_amount -= $sellerFee;
                        $adminWallet->commission_earned += $sellerFee;
                    } else {
                        // Remove the money from seller's wallet...
                        $sellerWallet = SellerWallet::where(['seller_id' => $detail->seller_id])->first();
                        $sellerWallet->escrow_balance -= $sellerFee;
                        $sellerWallet->total_earning += $sellerFee;
                        $sellerWallet->save();
                    }
                    $adminWallet->save();
                }
            });
            if ($transactionSuccess) {
                $update = $orderDetailQuery->update(['delivery_status' => 'delivered']);

                if ($update) {
                    // Get total number of order details and delivered order details for this order reference
                    $queryDetail = OrderDetail::whereJsonContains('order_info->reference', $orderReference);
                    $totalOrderDetails = $queryDetail->count();
                    $totalDeliveredDetails = $queryDetail->where('delivery_status', 'delivered')->count();
                    // If all order details are delivered, update the order status to delivered
                    if ($totalOrderDetails === $totalDeliveredDetails) {
                        Order::where('order_group_id', $orderReference)->update(['order_status' => 'delivered']);
                    }
                }
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $update = false;
        }
        return $update;
    }

    public static function generate_order($data)
    {
        $req = array_key_exists('request', $data) ? $data['request'] : null;
        $user = Helpers::get_customer($req);

        $is_guest = ($user == 'offline') ? 1 : 0;
        if ($req) {
            $is_guest = isset($req['is_guest']) && $req['is_guest']  ? 1 : 0;
        }

        $coupon_process = array(
            'discount' => 0,
            'coupon_bearer' => 'inhouse',
            'coupon_code' => 0,
            'coupon_type' => NULL,
        );

        if (!$is_guest && (isset($req['coupon_code']) && $req['coupon_code']) || session()->has('coupon_code')) {
            $coupon_code = $req['coupon_code'] ?? session('coupon_code');
            $coupon = Coupon::where(['code' => $coupon_code])
                ->where('status', 1)
                ->first();

            $coupon_process = $coupon ? self::coupon_process($data, $coupon) : $coupon_process;
        }

        $order_id = 100000 + Order::all()->count() + 1;
        if (Order::find($order_id)) {
            $order_id = Order::orderBy('id', 'DESC')->first()->id + 1;
        }
        $address_id = session('address_id') ? session('address_id') : null;
        $billing_address_id = session('billing_address_id') ? session('billing_address_id') : null;
        $coupon_code = $coupon_process['coupon_code'];
        $coupon_bearer = $coupon_process['coupon_bearer'];
        $discount = $coupon_process['discount'];
        $discount_type = $coupon_process['discount'] == 0 ? null : 'coupon_discount';
        $order_note = $req['order_note'] ?? session('order_note');

        $cart_group_id = $data['cart_group_id'];
        $admin_commission = (int)str_replace(",", "", Helpers::sales_commission_before_order($cart_group_id, $discount));

        $is_shipping_free = 0;
        $free_shipping_discount = 0;
        $free_shipping_type = NULL;
        $free_shipping_responsibility = NULL;
        $free_delivery = OrderManager::free_delivery_order_amount($cart_group_id);
        if($free_delivery['status'] && $free_delivery['shipping_cost_saved'] > 0  && $coupon_process['coupon_type'] !='free_delivery'){
            $is_shipping_free = 1;
            $free_shipping_discount = CartManager::get_shipping_cost($data['cart_group_id']);
            $free_shipping_type = 'free_shipping_over_order_amount';
            $free_shipping_responsibility = $free_delivery['responsibility'];
        }

        if ($req && session()->has('address_id') == false) {
            $address_id = isset($req['address_id']) ? $req['address_id'] : null;
        }

        if ($req && session()->has('billing_address_id') == false) {
            $billing_address_id = isset($req['billing_address_id']) ? $req['billing_address_id'] : null;
        }

        $seller_data = Cart::where(['cart_group_id' => $cart_group_id])->first();
        $shipping_method = CartShipping::where(['cart_group_id' => $cart_group_id])->first();
        if (isset($shipping_method)) {
            $shipping_method_id = $shipping_method->shipping_method_id;
        } else {
            $shipping_method_id = 0;
        }

        $shipping_model = Helpers::get_business_settings('shipping_method');
        if ($shipping_model == 'inhouse_shipping') {
            $admin_shipping = ShippingType::where('seller_id', 0)->first();
            $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
        } else {
            if ($seller_data->seller_is == 'admin') {
                $admin_shipping = ShippingType::where('seller_id', 0)->first();
                $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
            } else {
                $seller_shipping = ShippingType::where('seller_id', $seller_data->seller_id)->first();
                $shipping_type = isset($seller_shipping) == true ? $seller_shipping->shipping_type : 'order_wise';
            }
        }

        $guest_id = session('guest_id');
        if ($req) {
            $guest_id = $req['is_guest'] ? $req['guest_id'] : 0;
        }

        $customer_id = $user == 'offline' ? $guest_id : $user->id;

        $or = [
            'id' => $order_id,
            'verification_code' => rand(100000, 999999),
            'customer_id' => $customer_id,
            'is_guest' => $is_guest,
            'seller_id' => $seller_data->seller_id,
            'seller_is' => $seller_data->seller_is,
            'customer_type' => 'customer',
            'payment_status' => $data['payment_status'],
            'order_status' => $data['order_status'],
            'payment_method' => $data['payment_method'],
            'transaction_ref' => isset($data['transaction_ref']) ? $data['transaction_ref'] : null,
            'payment_by' => isset($data['payment_by']) ? $data['payment_by'] : NULL,
            'payment_note' => isset($data['payment_note']) ? $data['payment_note'] : NULL,
            'order_group_id' => $data['order_group_id'],
            'discount_amount' => $discount,
            'discount_type' => $discount_type,
            'coupon_code' => $coupon_code,
            'coupon_discount_bearer' => $coupon_bearer,
            'order_amount' => CartManager::cart_grand_total($cart_group_id) - $discount - $free_shipping_discount,
            'admin_commission' => $admin_commission,
            'shipping_address' => $address_id,
            'shipping_address_data' => ShippingAddress::find($address_id),
            'billing_address' => $billing_address_id,
            'billing_address_data' => ShippingAddress::find($billing_address_id),
            'shipping_responsibility' => Helpers::get_business_settings('shipping_method'),
            'shipping_cost' => CartManager::get_shipping_cost($data['cart_group_id']),
            'extra_discount' => $free_shipping_discount,
            'extra_discount_type' => $free_shipping_type,
            'free_delivery_bearer' => $seller_data->seller_is == 'seller' ? $free_shipping_responsibility : 'admin',
            'is_shipping_free' => $is_shipping_free,
            'shipping_method_id' => $shipping_method_id,
            'shipping_type' => $shipping_type,
            'created_at' => now(),
            'updated_at' => now(),
            'order_note' => $order_note
        ];

        if($data['payment_method'] == 'offline_payment')
        {
            OfflinePayments::insert([
                'order_id' => $order_id,
                'payment_info' => json_encode($data['offline_payment_info']),
                'created_at' => Carbon::now(),
            ]);
        }

//        confirmed
        DB::table('orders')->insertGetId($or);
        self::add_order_status_history($order_id, $customer_id, $data['payment_status'] == 'paid' ? 'confirmed' : 'pending', 'customer');
        $retrieveCommission = BusinessSetting::where('type', 'sales_commission')->first();
        $saleCommision = $retrieveCommission == null ? 0 : $retrieveCommission->value;

        foreach (CartManager::get_cart($data['cart_group_id']) as $c) {
            $product = Product::where(['id' => $c['product_id']])->first();
            $price = $c['tax_model'] == 'include' ? $c['price'] - $c['tax'] : $c['price'];
            $or_d = [
                'order_id' => $order_id,
                'product_id' => $c['product_id'],
                'seller_id' => $c['seller_id'],
                'product_details' => $product,
                'qty' => $c['quantity'],
                'price' => $price,
                'tax' => $c['tax'] * $c['quantity'],
                'tax_model' => $c['tax_model'],
                'discount' => $c['discount'] * $c['quantity'],
                'discount_type' => 'discount_on_product',
                'variant' => $c['variant'],
                'variation' => $c['variations'],
                'delivery_status' => 'pending',
                'order_info' => json_encode(["reference" => $data['order_group_id'], "commission_percentage" => $saleCommision]),
                'shipping_method_id' => null,
                'payment_status' => 'unpaid',
                'created_at' => now(),
                'updated_at' => now()
            ];

            if ($c['variant'] != null) {
                $type = $c['variant'];
                $var_store = [];
                foreach (json_decode($product['variation'], true) as $var) {
                    if ($type == $var['type']) {
                        $var['qty'] -= $c['quantity'];
                    }
                    array_push($var_store, $var);
                }
                Product::where(['id' => $product['id']])->update([
                    'variation' => json_encode($var_store),
                ]);
            }

            Product::where(['id' => $product['id']])->update([
                'current_stock' => $product['current_stock'] - $c['quantity']
            ]);

            DB::table('order_details')->insert($or_d);

        }

        if ($or['payment_method'] != 'cash_on_delivery' && $or['payment_method'] != 'offline_payment') {
            $order = Order::find($order_id);
            $order_summary = OrderManager::order_summary($order);
            $order_amount = $order_summary['subtotal'] - $order_summary['total_discount_on_product'] - $order['discount'];

            DB::table('order_transactions')->insert([
                'transaction_id' => OrderManager::gen_unique_id(),
                'customer_id' => $order['customer_id'],
                'seller_id' => $order['seller_id'],
                'seller_is' => $order['seller_is'],
                'order_id' => $order_id,
                'order_amount' => $order_amount,
                'seller_amount' => $order_amount - $admin_commission,
                'admin_commission' => $admin_commission,
                'received_by' => 'admin',
                'status' => 'hold',
                'delivery_charge' => $order['shipping_cost'] - $order['extra_discount'],
                'tax' => $order_summary['total_tax'],
                'delivered_by' => 'admin',
                'payment_method' => $or['payment_method'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (AdminWallet::where('admin_id', 1)->first() == false) {
                DB::table('admin_wallets')->insert([
                    'admin_id' => 1,
                    'withdrawn' => 0,
                    'commission_earned' => 0,
                    'inhouse_earning' => 0,
                    'delivery_charge_earned' => 0,
                    'pending_amount' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::table('admin_wallets')->where('admin_id', $order['seller_id'])->increment('pending_amount', $order['order_amount']);
        }

        if ($seller_data->seller_is == 'admin') {
            $seller = Admin::find($seller_data->seller_id);
        } else {
            $seller = Seller::find($seller_data->seller_id);
        }

        try {
            if(!$is_guest) {
                $fcm_token = $user->cm_firebase_token;

                $seller_fcm_token = $seller->cm_firebase_token;
                if ($data['payment_method'] != 'cash_on_delivery' && $or['payment_method'] != 'offline_payment') {
                    $value = Helpers::order_status_update_message('confirmed');
                } else {
                    $value = Helpers::order_status_update_message('pending');
                }

                if ($value) {
                    $data = [
                        'title' => translate('order'),
                        'description' => $value,
                        'order_id' => $order_id,
                        'image' => '',
                        'type'=>'order'
                    ];
                    Helpers::send_push_notif_to_device($fcm_token, $data);
                    Helpers::send_push_notif_to_device($seller_fcm_token, $data);
                }
            }

            $emailServices_smtp = Helpers::get_business_settings('mail_config');
            if ($emailServices_smtp['status'] == 0) {
                $emailServices_smtp = Helpers::get_business_settings('mail_config_sendgrid');
            }
            if ($emailServices_smtp['status'] == 1) {
                if($is_guest) {
                    $offline_user = ShippingAddress::where('id', $address_id)->first();
                    if(!$offline_user) {
                        $offline_user = ShippingAddress::find($billing_address_id);
                    }
                    $email = $offline_user->email;
                }else{
                    if ($req) {
                        $email = User::find($customer_id)->email;
                    }else{
                        $email = $user->email;
                    }
                }
                Mail::to($email)->send(new \App\Mail\OrderPlaced($order_id));
                Mail::to($seller->email)->send(new \App\Mail\OrderReceivedNotifySeller($order_id));
            }
        } catch (\Exception $exception) {

        }

        return $order_id;
    }

    /**
     * @param $data
     * @return int
     */
    public static function updated_generate_order($data) : int
    {
        $req = array_key_exists('request', $data) ? $data['request'] : null;
        $coupon_process = array(
            'discount' => 0,
            'coupon_bearer' => 'inhouse',
            'coupon_code' => 0,
        );
        if ((isset($req['coupon_code']) && $req['coupon_code']) || session()->has('coupon_code')) {
            $coupon_code = $req['coupon_code'] ?? session('coupon_code');
            $coupon = Coupon::where(['code' => $coupon_code])
                ->where('status', 1)
                ->first();

            $coupon_process = $coupon ? self::coupon_process($data, $coupon) : $coupon_process;
        }

        $address_id = session('address_id') ?? null;
        if ($req != null && !session()->has('address_id')) {
            $address_id = $req->has('address_id') ? $req['address_id'] : null;
        }

        $order_id = 100000 + Order::all()->count() + 1;
        $order_id = Order::find($order_id) ? Order::orderBy('id', 'DESC')->first()->id + 1 : $order_id;
        $billing_address_id = session('billing_address_id') ?? null;
        $coupon_code = $coupon_process['coupon_code'];
        $coupon_bearer = $coupon_process['coupon_bearer'];
        $discount = $coupon_process['discount'];
        $order_note = session()->has('order_note') ? session('order_note') : null;
        $cart_group_id = $data['cart_group_id'];
        $admin_commission = (int)str_replace(",", "", Helpers::sales_commission_before_order($cart_group_id, $discount));
        $user = Helpers::get_customer($req);
        $seller_data = Cart::where(['cart_group_id' => $cart_group_id])->first();
        $shipping_method = CartShipping::where(['cart_group_id' => $cart_group_id])->first();
        $shipping_method_id = isset($shipping_method) ? $shipping_method->shipping_method_id : 0;
        $shipping_model = Helpers::get_business_settings('shipping_method');

        if ($shipping_model == 'inhouse_shipping') {
            $admin_shipping = ShippingType::where('seller_id', 0)->first();
            $shipping_type = isset($admin_shipping) ? $admin_shipping->shipping_type : 'order_wise';
        } else {
            if ($seller_data->seller_is == 'admin') {
                $admin_shipping = ShippingType::where('seller_id', 0)->first();
                $shipping_type = isset($admin_shipping) ? $admin_shipping->shipping_type : 'order_wise';
            } else {
                $seller_shipping = ShippingType::where('seller_id', $seller_data->seller_id)->first();
                $shipping_type = isset($seller_shipping) ? $seller_shipping->shipping_type : 'order_wise';
            }
        }

        $order_data = [
            'order_id' => $order_id,
            'user_id' => $user->id,
            'seller_id' => $seller_data->seller_id,
            'seller_is' => $seller_data->seller_is,
            'data' => $data,
            'discount' => $discount,
            'coupon_code' => $coupon_code,
            'coupon_bearer' => $coupon_bearer,
            'cart_group_id' => $cart_group_id,
            'admin_commission' => $admin_commission,
            'address_id' => $address_id,
            'billing_address_id' => $billing_address_id,
            'shipping_method_id' => $shipping_method_id,
            'shipping_type' => $shipping_type,
            'order_note' => $order_note,
        ];

        //order data insert
        self::order_insert($order_data);

        $seller = $seller_data->seller_is == 'admin' ? Admin::find($seller_data->seller_id) : Seller::find($seller_data->seller_id);

        try {
            $fcm_token = $user->cm_firebase_token;
            $seller_fcm_token = $seller->cm_firebase_token;
            if ($data['payment_method'] != 'cash_on_delivery' && $data['payment_method'] != 'offline_payment') {
                $value = Helpers::order_status_update_message('confirmed');
            } else {
                $value = Helpers::order_status_update_message('pending');
            }

            if ($value) {
                $data = [
                    'title' => translate('order'),
                    'description' => $value,
                    'order_id' => $order_id,
                    'image' => '',
                    'type'=>'order'
                ];
                Helpers::send_push_notif_to_device($fcm_token, $data);
                Helpers::send_push_notif_to_device($seller_fcm_token, $data);
            }

            $emailServices_smtp = Helpers::get_business_settings('mail_config');
            if ($emailServices_smtp['status'] == 0) {
                $emailServices_smtp = Helpers::get_business_settings('mail_config_sendgrid');
            }
            if ($emailServices_smtp['status'] == 1) {
                Mail::to($user->email)->send(new \App\Mail\OrderPlaced($order_id));
                Mail::to($seller->email)->send(new \App\Mail\OrderReceivedNotifySeller($order_id));
            }
        } catch (\Exception $exception) {

        }

        return $order_id;
    }

    /**
     * @param $order_data
     * @return int
     * order related insert into
     */
    public static function order_insert($order_data) : int
    {

        //order data insert start
        $or = [
            'id' => $order_data['order_id'],
            'verification_code' => rand(100000, 999999),
            'customer_id' => $order_data['user_id'],
            'seller_id' => $order_data['seller_id'],
            'seller_is' => $order_data['seller_is'],
            'customer_type' => 'customer',
            'payment_status' => $order_data['data']['payment_status'],
            'order_status' => $order_data['data']['order_status'],
            'payment_method' => $order_data['data']['payment_method'],
            'transaction_ref' => $order_data['data']['transaction_ref'],
            'payment_by' => $order_data['data']['payment_by'] ?? NULL,
            'payment_note' => $order_data['data']['payment_note'] ?? NULL,
            'order_group_id' => $order_data['data']['order_group_id'],
            'discount_amount' => $order_data['discount'],
            'discount_type' => $order_data['discount'] == 0 ? null : 'coupon_discount',
            'coupon_code' => $order_data['coupon_code'],
            'coupon_discount_bearer' => $order_data['coupon_bearer'],
            'order_amount' => CartManager::cart_grand_total($order_data['cart_group_id']) - $order_data['discount'],
            'admin_commission' => $order_data['admin_commission'],
            'shipping_address' => $order_data['address_id'],
            'shipping_address_data' => ShippingAddress::find($order_data['address_id']),
            'billing_address' => $order_data['billing_address_id'],
            'billing_address_data' => ShippingAddress::find($order_data['billing_address_id']),
            'shipping_cost' => CartManager::get_shipping_cost($order_data['data']['cart_group_id']),
            'shipping_method_id' => $order_data['shipping_method_id'],
            'shipping_type' => $order_data['shipping_type'],
            'created_at' => now(),
            'updated_at' => now(),
            'order_note' => $order_data['order_note']
        ];
        DB::table('orders')->insertGetId($or);
        //order data insert end

        //order status history data insert
        self::add_order_status_history($order_data['order_id'], auth('customer')->id(), $order_data['data']['payment_status'] == 'paid' ? 'confirmed' : 'pending', 'customer');

        //order products info insert into order_details table start
        foreach (CartManager::get_cart($order_data['data']['cart_group_id']) as $c) {
            $product = Product::where(['id' => $c['product_id']])->first();
            $price = $c['tax_model'] == 'include' ? $c['price'] - $c['tax'] : $c['price'];
            $or_d = [
                'order_id' => $order_data['order_id'],
                'product_id' => $c['product_id'],
                'seller_id' => $c['seller_id'],
                'product_details' => $product,
                'qty' => $c['quantity'],
                'price' => $price,
                'tax' => $c['tax'] * $c['quantity'],
                'tax_model' => $c['tax_model'],
                'discount' => $c['discount'] * $c['quantity'],
                'discount_type' => 'discount_on_product',
                'variant' => $c['variant'],
                'variation' => $c['variations'],
                'delivery_status' => 'pending',
                'shipping_method_id' => null,
                'payment_status' => 'unpaid',
                'created_at' => now(),
                'updated_at' => now()
            ];

            if ($c['variant'] != null) {
                $type = $c['variant'];
                $var_store = [];
                foreach (json_decode($product['variation'], true) as $var) {
                    if ($type == $var['type']) {
                        $var['qty'] -= $c['quantity'];
                    }
                    $var_store[] = $var;
                }

                Product::where(['id' => $product['id']])->update([
                    'variation' => json_encode($var_store),
                ]);
            }

            Product::where(['id' => $product['id']])->update([
                'current_stock' => $product['current_stock'] - $c['quantity']
            ]);

            DB::table('order_details')->insert($or_d);
        }
        //order products info insert into order_details table end

        if ($or['payment_method'] != 'cash_on_delivery' && $or['payment_method'] != 'offline_payment') {
            $order = Order::find($order_data['order_id']);
            $order_summary = OrderManager::order_summary($order);
            $order_amount = $order_summary['subtotal'] - $order_summary['total_discount_on_product'] - $order['discount'];

            //order transaction data insert
            DB::table('order_transactions')->insert([
                'transaction_id' => OrderManager::gen_unique_id(),
                'customer_id' => $order['customer_id'],
                'seller_id' => $order['seller_id'],
                'seller_is' => $order['seller_is'],
                'order_id' => $order_data['order_id'],
                'order_amount' => $order_amount,
                'seller_amount' => $order_amount - $order_data['admin_commission'],
                'admin_commission' => $order_data['admin_commission'],
                'received_by' => 'admin',
                'status' => 'hold',
                'delivery_charge' => $order['shipping_cost'],
                'tax' => $order_summary['total_tax'],
                'delivered_by' => 'admin',
                'payment_method' => $or['payment_method'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            //admin wallet data insert
            if (!AdminWallet::where('admin_id', 1)->first()) {
                DB::table('admin_wallets')->insert([
                    'admin_id' => 1,
                    'withdrawn' => 0,
                    'commission_earned' => 0,
                    'inhouse_earning' => 0,
                    'delivery_charge_earned' => 0,
                    'pending_amount' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::table('admin_wallets')->where('admin_id', $order['seller_id'])->increment('pending_amount', $order['order_amount']);
        }

        return $order_data['order_id'];
    }

    /**
     * @param $order_data
     * @return int
     * order related insert into
     */
    public static function order_again($request)
    {
        $order_products = OrderDetail::where('order_id', $request->order_id)->get();
        $order_product_count = $order_products->count();
        $add_to_cart_count = 0;

        foreach ($order_products as $key=>$order_product) {
            $product = Product::active()->find($order_product->product_id);

            if($product) {
                $product_valid = true;
                if (($product['product_type'] == 'physical') && (($product['current_stock'] < $order_product['qty']) || ($product['minimum_order_qty'] > $product['current_stock']))) {
                    $product_valid = false;
                }
                if ($product_valid) {
                    $color = null;
                    $choices = [];
                    if ($order_product->variation) {
                        $variation = json_decode($order_product->variation, true);

                        if (isset($variation['color']) && $variation['color']) {
                            $color = Color::where('name', $variation['color'])->first()->code;
                            $i = 1;
                            foreach ($variation as $key => $var) {
                                if ($key != 'color') {
                                    $choices['choice_' . $i] = $var;
                                    $i++;
                                }
                            }
                        } else {
                            $i = 1;
                            foreach ($variation as $key => $var) {
                                $choices['choice_' . $i] = $var;
                                $i++;
                            }
                        }
                    }

                    $user = Helpers::get_customer($request);
                    //generate group id
                    $cart_check = Cart::where([
                        'customer_id' => $user->id,
                        'seller_id' => ($product->added_by == 'admin') ? 1 : $product->user_id,
                        'seller_is' => $product->added_by])->first();

                    if (isset($cart_check)) {
                        $cart_group_id = $cart_check['cart_group_id'];
                    } else {
                        $cart_group_id = $user->id . '-' . Str::random(5) . '-' . time();
                    }
                    //generate group id end

                    $price = 0;
                    if (json_decode($product->variation)) {
                        $count = count(json_decode($product->variation));

                        for ($i = 0; $i < $count; $i++) {
                            if (json_decode($product->variation)[$i]->type == $order_product->variant) {
                                $price = json_decode($product->variation)[$i]->price;

                                if (json_decode($product->variation)[$i]->qty < $order_product->qty) {
                                    $product_valid = false;
                                }
                            }
                        }
                    } else {
                        $price = $product->unit_price;
                    }

                    $tax = Helpers::tax_calculation($price, $product['tax'], 'percent');
                    if ($product_valid && $price != 0) {
                        $cart_exist = Cart::where(['customer_id'=>$user->id, 'variations'=>$order_product->variation, 'product_id'=>$order_product->product_id])->first();
                        if(!$cart_exist){
                            $order_product_qty = $order_product->qty < $product['minimum_order_qty'] ? $product['minimum_order_qty'] : $order_product->qty;

                            $cart = new Cart();
                            $cart['cart_group_id'] = $cart_group_id;
                            $cart['color'] = $color;
                            $cart['product_id'] = $order_product->product_id;
                            $cart['product_type'] = $product->product_type;
                            $cart['choices'] = json_encode($choices);
                            $cart['variations'] = $order_product->variation;
                            $cart['variant'] = $order_product->variant;
                            $cart['customer_id'] = $user->id ?? 0;
                            $cart['quantity'] = $order_product_qty;
                            $cart['price'] = $price;
                            $cart['tax'] = $tax;
                            $cart['tax_model'] = $product->tax_model;
                            $cart['slug'] = $product->slug;
                            $cart['name'] = $product->name;
                            $cart['discount'] = Helpers::get_product_discount($product, $price);
                            $cart['thumbnail'] = $product->thumbnail;
                            $cart['seller_id'] = ($product->added_by == 'admin') ? 1 : $product->user_id;
                            $cart['seller_is'] = $product->added_by;
                            $cart['shipping_cost'] = $product->product_type == 'physical' ? CartManager::get_shipping_cost_for_product_category_wise($product, $order_product_qty) : 0;
                            if ($product->added_by == 'seller') {
                                $cart['shop_info'] = Shop::where(['seller_id' => $product->user_id])->first()->name;
                            } else {
                                $cart['shop_info'] = Helpers::get_business_settings('company_name');
                            }

                            $shippingMethod = Helpers::get_business_settings('shipping_method');

                            if ($shippingMethod == 'inhouse_shipping') {
                                $admin_shipping = ShippingType::where('seller_id', 0)->first();
                                $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';

                            } else {
                                if ($product->added_by == 'admin') {
                                    $admin_shipping = ShippingType::where('seller_id', 0)->first();
                                    $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
                                } else {
                                    $seller_shipping = ShippingType::where('seller_id', $product->user_id)->first();
                                    $shipping_type = isset($seller_shipping) == true ? $seller_shipping->shipping_type : 'order_wise';
                                }
                            }

                            $cart['shipping_type'] = $shipping_type;
                            $cart->save();
                        }
                        $add_to_cart_count++;
                    }
                }
            }
        }

        return [
            'order_product_count' => $order_product_count,
            'add_to_cart_count' => $add_to_cart_count,
        ];
    }

    public static function minimum_order_amount_verify($request, $cart_group_id = null)
    {
        $user = Helpers::get_customer($request);
        $status = 1;
        $amount = 0;
        $minimum_order_amount = 0;
        $minimum_order_amount_status = Helpers::get_business_settings('minimum_order_amount_status');
        $minimum_order_amount_by_seller = Helpers::get_business_settings('minimum_order_amount_by_seller');
        $inhouse_minimum_order_amount = Helpers::get_business_settings('minimum_order_amount');
        $decimal_point_settings = Helpers::get_business_settings('decimal_point_settings');

        if($minimum_order_amount_status) {
            $query = Cart::with(['seller', 'all_product'])
                ->where([
                    'customer_id' => ($user == 'offline' ? (session('guest_id') ?? $request->guest_id) : $user->id),
                    'is_guest' => ($user == 'offline' ? 1 : '0'),
                ]);
            if ($cart_group_id) {
                $cart_item = $query->where('cart_group_id', $cart_group_id)->first();
                if ($cart_item->all_product->added_by == 'admin') {
                    $minimum_order_amount = $inhouse_minimum_order_amount;
                } else {
                    $minimum_order_amount = $minimum_order_amount_by_seller ? $cart_item->seller->minimum_order_amount : 0;
                }

                $amount = CartManager::cart_grand_total($cart_group_id);
                $status = $minimum_order_amount > $amount ? 0 : 1;

            } else {
                $cart_groups = $query->get()->groupBy('cart_group_id');
                foreach ($cart_groups as $group_key => $cart_group) {
                    $seller = $cart_group[0]->seller_is;
                    if ($seller == 'admin') {
                        $minimum_order_amount = $inhouse_minimum_order_amount;
                    } else {
                        $minimum_order_amount = $minimum_order_amount_by_seller ? $cart_group[0]->seller->minimum_order_amount : 0;
                    }

                    $new_amount = CartManager::cart_grand_total($group_key);
                    ($minimum_order_amount > $new_amount ? $status = 0 : '');
                    $amount = $amount + $new_amount;
                }
            }
        }

        $data = [
            'minimum_order_amount'=> $minimum_order_amount ?? 0,
            'amount'=>$amount ? floatval($amount) : 0,
            'status'=>$status,
            'cart_group_id'=>$cart_group_id ?? null
        ];

        return $data;
    }


    public static function free_delivery_order_amount($cart_group_id = null)
    {
        $free_delivery = [
            'status'=> 0, // full-fill the requirement if status is 1
            'amount'=> 0, // free delivery amount
            'percentage'=> 0, // completed percentage
            'amount_need'=> 0, // need amount for free delivery
            'shipping_cost_saved' => 0,
            'cart_id' => $cart_group_id
        ];

        $free_delivery['status'] = Helpers::get_business_settings('free_delivery_status');
        $free_delivery['responsibility'] = Helpers::get_business_settings('free_delivery_responsibility');
        $free_delivery_over_amount = Helpers::get_business_settings('free_delivery_over_amount');
        $free_delivery_over_amount_seller = Helpers::get_business_settings('free_delivery_over_amount_seller');

        if($free_delivery['status'] && $cart_group_id)
        {
            $get_cart = Cart::where(['product_type'=>'physical'])->where('cart_group_id', $cart_group_id)->first();

            if($get_cart)
            {
                if($get_cart->seller_is == 'admin')
                {
                    $free_delivery['amount'] = $free_delivery_over_amount;
                    $free_delivery['status'] = $free_delivery_over_amount > 0 ? 1:0;
                }else{
                    $seller = Seller::where('id', $get_cart->seller_id)->first();
                    $free_delivery['status'] = $seller->free_delivery_status ?? 0;

                    if($free_delivery['responsibility'] == 'admin')
                    {
                        $free_delivery['amount'] = $free_delivery_over_amount_seller;
                        $free_delivery['status'] = $free_delivery_over_amount_seller > 0 ? 1:0;
                    }

                    if($free_delivery['responsibility'] == 'seller' && $free_delivery['status'] == 1){
                        $free_delivery['amount'] = $seller->free_delivery_over_amount;
                        $free_delivery['status'] = $seller->free_delivery_over_amount > 0 ? 1:0;
                    }
                }

                $amount = CartManager::cart_grand_total_without_shipping_charge($get_cart->cart_group_id);
                $free_delivery['amount_need'] = $free_delivery['amount'] - $amount;
                $free_delivery['percentage'] = ($free_delivery['amount'] > 0) && $amount > 0 && ($free_delivery['amount'] >= $amount) ? number_format(($amount/ $free_delivery['amount']) * 100) : 100;
                if($free_delivery['status'] == 1 && $free_delivery['percentage'] == 100)
                {
                    $free_delivery['shipping_cost_saved'] = CartManager::get_shipping_cost($get_cart->cart_group_id);
                }
            }else{
                $free_delivery['status'] = 0;
            }
        }

        return $free_delivery;
    }
}
