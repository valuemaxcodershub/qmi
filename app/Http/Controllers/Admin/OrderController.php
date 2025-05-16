<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Exception;
use Carbon\Carbon;
use App\CPU\Convert;
use App\CPU\Helpers;
use App\Model\Order;
use App\Model\Seller;
use Ramsey\Uuid\Uuid;
use App\Model\Customer;
use Carbon\CarbonPeriod;
use App\CPU\ImageManager;
use App\CPU\OrderManager;
use App\CPU\BackEndHelper;
use App\Model\DeliveryMan;
use App\Model\OrderDetail;
use App\Traits\CommonTrait;
use App\CPU\CustomerManager;
use Illuminate\Http\Request;
use App\Model\BusinessSetting;
use App\Model\DeliveryZipCode;
use App\Model\ShippingAddress;
use App\Model\OrderTransaction;
use function App\CPU\translate;
use App\Model\DeliverymanWallet;
use Illuminate\Http\JsonResponse;
use App\Models\User as ModelsUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\View;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Model\DeliveryManTransaction;

class OrderController extends Controller
{
    use CommonTrait;
    public function __construct(
        private DeliveryZipCode $delivery_zip_code,
        private Order $order,
        private Seller $seller,
        private User $user,
    ){

    }

    public function list(Request $request, $status) {

        $search = $request['search'];
        $filter = $request['filter'];
        $date_type  = $request['date_type'] ?? 'this_year';
        $from = $request['from'];
        $to = $request['to'];
        $key = $request['search'] ? explode(' ', $request['search']) : '';
        $delivery_man_id = $request['delivery_man_id'];
        
        
        $seller_id = $request->seller_id;
        $customer_id = $request->customer_id;

        $orders = Order::with(['customer', 'seller.shop'])
                        ->when($status != 'all', function ($q) use($status){
                            $q->where(function ($query) use ($status) {
                                $query->orWhere('order_status', $status);
                            });  
                        })
                        // ->select(DB::raw('SUM(order_amount) as total_amount'), 'order_group_id')
                        ->groupBy('order_group_id')
                        ->latest('id')
                        ->paginate(Helpers::pagination_limit());
            $orders->getCollection()->map(function ($order) {
                $order->total_amount = self::totalAmountByGroupId($order->order_group_id);
                return $order;
            });
            
        return view(
                'admin-views.order.list',
                compact('orders', 'status', 'search', 'from', 'filter', 'date_type', 'to', 'from', 'key',
                         'delivery_man_id', 'seller_id', 'customer_id'
                        )
        );
    }

    public function totalAmountByGroupId($order_group_id) {
        $order = Order::where('order_group_id', $order_group_id)->sum('order_amount');
        return (float) $order;
    }

    public function viewOrder($id) {
        $company_name =BusinessSetting::where('type', 'company_name')->first()->value;
        $company_web_logo =BusinessSetting::where('type', 'company_web_logo')->first()->value;
        $reference = $id;
        
        $orders = $this->order->with('details.product_all_status', 'verification_images' ,'shipping', 'seller.shop', 'offline_payments','delivery_man')
                    ->where(['order_group_id' => $id])->get();

        if ($orders->count() < 1) {
            Toastr::error("Order ($id) not found");
            return redirect()->route('admin.orders.list', ['status' => 'pending']);
        }
                    
        $physical_product = false;
        if ($orders->count() > 0) {
            foreach ($orders as $order) {
                $order_details[] = $order->details;
                
                foreach($order->details as $product){
                    if(isset($product->product) && $product->product->product_type == 'physical'){
                        $physical_product = true;
                    }
                }
            }
        }

        $shipping_method = Helpers::get_business_settings('shipping_method');
        $delivery_men = DeliveryMan::where('is_active', 1)->when($order->seller_is == 'admin', function ($query) {
                    $query->where(['seller_id' => 0]);
                })->when($order->seller_is == 'seller' && $shipping_method == 'sellerwise_shipping', function ($query) use ($order) {
                    $query->where(['seller_id' => $order['seller_id']]);
                })->when($order->seller_is == 'seller' && $shipping_method == 'inhouse_shipping', function ($query) use ($order) {
                    $query->where(['seller_id' => 0]);
                })->get();
        // $total_delivered = Order::where(['seller_id' => $order[0]->seller_id, 'order_status' => 'delivered', 'order_type' => 'default_type'])->count();
        $total_delivered = mt_rand(1111, 9090);
        return view('admin-views.order.view-order', compact('orders', 'company_name', 'company_web_logo', 'reference', 'physical_product', 'delivery_men', 'total_delivered'));
    }

    // public function list(Request $request, $status)
    // {

    //     $search = $request['search'];
    //     $filter = $request['filter'];
    //     $date_type  = $request['date_type'] ?? 'this_year';
    //     $from = $request['from'];
    //     $to = $request['to'];
    //     $key = $request['search'] ? explode(' ', $request['search']) : '';
    //     $delivery_man_id = $request['delivery_man_id'];

    //     Order::where(['checked' => 0])->update(['checked' => 1]);

    //     $orders = Order::with(['customer', 'seller.shop'])
    //         ->when($status != 'all', function ($q) use($status){
    //             $q->where(function ($query) use ($status) {
    //                 $query->orWhere('order_status', $status);
    //             });
    //         })
    //         ->when($filter,function($q) use($filter){
    //             $q->when($filter == 'all', function($q){
    //                 return $q;
    //             })
    //                 ->when($filter == 'POS', function ($q){
    //                     $q->whereHas('details', function ($q){
    //                         $q->where('order_type', 'POS');
    //                     });
    //                 })
    //                 ->when($filter == 'admin' || $filter == 'seller', function($q) use($filter){
    //                     $q->whereHas('details', function ($query) use ($filter){
    //                         $query->whereHas('product', function ($query) use ($filter){
    //                             $query->where('added_by', $filter);
    //                         });
    //                     });
    //                 });
    //         })
    //         ->when($request->has('search') && $search!=null,function ($q) use ($key) {
    //             $q->where(function($qq) use ($key){
    //                 foreach ($key as $value) {
    //                     $qq->where('id', 'like', "%{$value}%")
    //                         ->orWhere('order_status', 'like', "%{$value}%")
    //                         ->orWhere('transaction_ref', 'like', "%{$value}%");
    //                 }});
    //         })
    //         ->when($request->has('date_type')&& $request->date_type == "this_year", function($dateQuery) {
    //             $current_start_year = date('Y-01-01');
    //             $current_end_year = date('Y-12-31');
    //             $dateQuery->whereDate('created_at', '>=',$current_start_year)
    //                 ->whereDate('created_at', '<=',$current_end_year);
    //         })
    //         ->when($request->has('date_type')&& $request->date_type == "this_month", function($dateQuery) {
    //             $current_month_start = date('Y-m-01');
    //             $current_month_end = date('Y-m-t');
    //             $dateQuery->whereDate('created_at', '>=',$current_month_start)
    //                 ->whereDate('created_at', '<=',$current_month_end);
    //         })
    //         ->when($request->has('date_type')&& $request->date_type == "this_week", function($dateQuery) {
    //             $start_week = Carbon::now()->subDays(7)->startOfWeek()->format('Y-m-d');
    //             $end_week =Carbon::now()->startOfWeek()->format('Y-m-d');
    //             $dateQuery->whereDate('created_at', '>=',$start_week)
    //             ->whereDate('created_at', '<=',$end_week );
    //         })
    //         ->when($request->has('date_type')&& $request->date_type == "custom_date" && !empty($from) && !empty($to), function($dateQuery) use($from, $to) {
    //             $dateQuery->whereDate('created_at', '>=',$from)
    //                 ->whereDate('created_at', '<=',$to);
    //         })
    //         ->when($delivery_man_id, function ($q) use($delivery_man_id){
    //             $q->where(['delivery_man_id'=> $delivery_man_id]);
    //         })
    //         ->when($request->customer_id != 'all' && $request->has('customer_id') ,function($query)use($request){
    //             return $query->where('customer_id',$request->customer_id);
    //         })
    //         ->when($request->seller_id != 'all' && $request->has('seller_id') && $request->seller_id != 0 ,function($query)use($request){
    //             return $query->where(['seller_is'=>'seller','seller_id'=>$request->seller_id]);
    //         })
    //         ->when($request->seller_id != 'all' && $request->has('seller_id') && $request->seller_id == 0 ,function($query)use($request){
    //             return $query->where(['seller_is'=>'admin']);
    //         })
    //         ->latest('id')
    //         ->paginate(Helpers::pagination_limit())
    //         ->appends([
    //             'search'=>$request['search'],
    //             'filter'=>$request['filter'],'from'=>$request['from'],
    //             'to'=>$request['to'],
    //             'date_type' =>$request['date_type'],
    //             'customer_id'=> $request->customer_id,
    //             'seller_id' => $request->seller_id,
    //             'delivery_man_id'=>$request['delivery_man_id'],
    //             ]);

    //         $pending_query = Order::where(['order_status' => 'pending']);
    //         $pending_count = $this->common_query_status_count($pending_query, $status, $request);

    //         $confirmed_query = Order::where(['order_status' => 'confirmed']);
    //         $confirmed_count = $this->common_query_status_count($confirmed_query, $status, $request);

    //         $processing_query = Order::where(['order_status' => 'processing']);
    //         $processing_count = $this->common_query_status_count($processing_query, $status, $request);

    //         $out_for_delivery_query = Order::where(['order_status' => 'out_for_delivery']);
    //         $out_for_delivery_count = $this->common_query_status_count($out_for_delivery_query, $status, $request);

    //         $delivered_query = Order::where(['order_status' => 'delivered']);
    //         $delivered_count = $this->common_query_status_count($delivered_query, $status, $request);

    //         $canceled_query = Order::where(['order_status' => 'canceled']);
    //         $canceled_count = $this->common_query_status_count($canceled_query, $status, $request);

    //         $returned_query = Order::where(['order_status' => 'returned']);
    //         $returned_count = $this->common_query_status_count($returned_query, $status, $request);

    //         $failed_query = Order::where(['order_status' => 'failed']);
    //         $failed_count = $this->common_query_status_count($failed_query, $status, $request);

    //         $sellers = $this->seller->with('shop')->where('status','!=','pending')->get();

    //         $customer = "all";
    //         if($request->customer_id != 'all' && !is_null($request->customer_id) && $request->has('customer_id')){
    //             $customer = $this->user->find($request->customer_id);
    //         }

    //         $seller_id = $request->seller_id;
    //         $customer_id = $request->customer_id;

    //     return view(
    //             'admin-views.order.list',
    //             compact(
    //                 'date_type',
    //                 'orders',
    //                 'search',
    //                 'from', 'to', 'status',
    //                 'filter',
    //                 'pending_count',
    //                 'confirmed_count',
    //                 'processing_count',
    //                 'out_for_delivery_count',
    //                 'delivered_count',
    //                 'returned_count',
    //                 'failed_count',
    //                 'canceled_count',
    //                 'sellers',
    //                 'customer',
    //                 'seller_id',
    //                 'customer_id',
    //             )
    //         );
    // }

    public function common_query_status_count($query, $status, $request){
        $search = $request['search'];
        $filter = $request['filter'];
        $from = $request['from'];
        $to = $request['to'];
        $key = $request['search'] ? explode(' ', $request['search']) : '';

            return $query->when($status != 'all', function ($q) use($status){
                $q->where(function ($query) use ($status) {
                    $query->orWhere('order_status', $status);
                });
            })
            ->when($filter,function($q) use($filter) {
                $q->when($filter == 'all', function ($q) {
                    return $q;
                })
                ->when($filter == 'POS', function ($q){
                    $q->whereHas('details', function ($q){
                        $q->where('order_type', 'POS');
                    });
                })
                ->when($filter == 'admin' || $filter == 'seller', function($q) use($filter){
                    $q->whereHas('details', function ($query) use ($filter){
                        $query->whereHas('product', function ($query) use ($filter){
                            $query->where('added_by', $filter);
                        });
                    });
                });
            })
            ->when($request->has('search') && $search!=null,function ($q) use ($key) {
                $q->where(function($qq) use ($key){
                    foreach ($key as $value) {
                        $qq->where('id', 'like', "%{$value}%")
                            ->orWhere('order_status', 'like', "%{$value}%")
                            ->orWhere('transaction_ref', 'like', "%{$value}%");
                    }});
            })->when(!empty($from) && !empty($to), function($dateQuery) use($from, $to) {
                $dateQuery->whereDate('created_at', '>=',$from)
                    ->whereDate('created_at', '<=',$to);
            })->count();
    }

    // public function details($id)
    // {
    //     //for edit  address
    //     $country_restrict_status = Helpers::get_business_settings('delivery_country_restriction');
    //     $zip_restrict_status = Helpers::get_business_settings('delivery_zip_code_area_restriction');
    //     $countries = $country_restrict_status ? $this->get_delivery_country_array() : COUNTRIES;
    //     $zip_codes = $zip_restrict_status ? $this->delivery_zip_code->all() : 0;

    //     $company_name =BusinessSetting::where('type', 'company_name')->first()->value;
    //     $company_web_logo =BusinessSetting::where('type', 'company_web_logo')->first()->value;

    //     $order = $this->order->with('details.product_all_status', 'verification_images' ,'shipping', 'seller.shop', 'offline_payments','delivery_man')->where(['id' => $id])->first();

    //     $physical_product = false;
    //     foreach($order->details as $product){
    //         if(isset($product->product) && $product->product->product_type == 'physical'){
    //             $physical_product = true;
    //         }
    //     }

    //     $linked_orders = Order::where(['order_group_id' => $order['order_group_id']])
    //         ->whereNotIn('order_group_id', ['def-order-group'])
    //         ->whereNotIn('id', [$order['id']])
    //         ->get();

    //     $total_delivered = Order::where(['seller_id' => $order->seller_id, 'order_status' => 'delivered', 'order_type' => 'default_type'])->count();

    //     $shipping_method = Helpers::get_business_settings('shipping_method');
    //     $delivery_men = DeliveryMan::where('is_active', 1)->when($order->seller_is == 'admin', function ($query) {
    //         $query->where(['seller_id' => 0]);
    //     })->when($order->seller_is == 'seller' && $shipping_method == 'sellerwise_shipping', function ($query) use ($order) {
    //         $query->where(['seller_id' => $order['seller_id']]);
    //     })->when($order->seller_is == 'seller' && $shipping_method == 'inhouse_shipping', function ($query) use ($order) {
    //         $query->where(['seller_id' => 0]);
    //     })->get();

    //     $shipping_address = ShippingAddress::find($order->shipping_address);
    //     if($order->order_type == 'default_type')
    //     {
    //         return view('admin-views.order.order-details', compact('shipping_address','order', 'linked_orders',
    //             'delivery_men', 'total_delivered', 'company_name', 'company_web_logo', 'physical_product',
    //             'country_restrict_status','zip_restrict_status','countries','zip_codes'));
    //     }else{
    //         return view('admin-views.pos.order.order-details', compact('order', 'company_name', 'company_web_logo'));
    //     }

    // }

    public function add_delivery_man($order_id, $delivery_man_id)
    {
        if ($delivery_man_id == 0) {
            return response()->json([], 401);
        }
                
        Order::where(['order_group_id' => $order_id])->update([
            'delivery_man_id' => $delivery_man_id,
            'delivery_type' => 'self_delivery',
            'delivery_service_name' => null,
            'third_party_delivery_tracking_id' => null,
        ]); 
        
        $order = Order::where(['order_group_id' => $order_id])->first();
        $fcm_token = isset($order->delivery_man) ? $order->delivery_man->fcm_token : null;
        $value = Helpers::order_status_update_message('del_assign') . " ID: " . $order['id'];
        if(!empty($fcm_token)) {
            try {
                if ($value != null) {
                    $data = [
                        'title' => translate('order'),
                        'description' => $value,
                        'order_id' => $order['id'],
                        'image' => '',
                        'type'=>'order'
                    ];

                    if ($order->delivery_man_id) {
                        self::add_deliveryman_push_notification($data, $order->delivery_man_id);
                    }
                    Helpers::send_push_notif_to_device($fcm_token, $data);
                }
            } catch (\Exception $e) {
                Toastr::warning(translate('push_notification_failed_for_DeliveryMan'));
            }
        }

        return response()->json(['status' => true], 200);
    }

    public function updateOrderStatus(Request $request) {
        $txIdType = $request->tx_id_type;
        // return $request;
        $reference = $txIdType == 'reference' ? $request->reference : $request->id;
        switch ($txIdType) {
            case 'reference':
                $order = Order::with(['details'])->where(['order_group_id' => $reference])->get();
                $singleOrder = $order[0];
            break;
            case 'order_id': 
                $order = Order::with(['details'])->find($reference);
                $singleOrder = $order;
            break;
        } 

        if($request->order_status=='delivered' && $singleOrder->payment_status !='paid'){
            return response()->json(["message" => "You cannot mark order as delivered. Payment not done", "status" => false], 400);
        }

        if($singleOrder->order_status =='delivered'){
            return response()->json(["message" => "Order delivered already", "status" => false], 400);
        }
        return self::updateOrderDetails($reference, $request->order_status);
    }

    private function updateOrderDetails($groupId, $status) : JsonResponse {
        try {
            $orders = $this->order->with(['details'])->where(['order_group_id' => $groupId])->get();
            $singleOrder = $orders[0];
            $paymentStatus = $singleOrder['payment_status'];
            $orderDetailUpdated = 0;

            if ($status == 'delivered') {
                $result = OrderManager::distribute_delivered_order_funds($groupId);
                if ($result) {
                    return response()->json(["message" => "Order(s) updated successfully", "status" => true], 200);
                }
                return response()->json(["message" => "Order status could not be updated", "status" => false], 400);
            }
    
            foreach ($orders as $order) {
                foreach ($order->details as $detail) {
                    $updateOrderDetail = false;
    
                    if ($status == 'pending') {
                        $updateOrderDetail = OrderDetail::where(['id' => $detail->id, 'delivery_status' => 'pending'])
                            ->update(['delivery_status' => 'pending', 'payment_status' => $paymentStatus]);
                    } elseif ($status == 'confirmed') {
                        $updateOrderDetail = OrderDetail::where(['id' => $detail->id, 'delivery_status' => 'pending'])
                            ->update(['delivery_status' => 'confirmed', 'payment_status' => $paymentStatus]);
                    } elseif ($status == 'processing') {
                        $updateOrderDetail = OrderDetail::where(['id' => $detail->id])
                            ->whereIn('delivery_status', ['pending', 'confirmed', 'processing'])
                            ->update(['delivery_status' => 'processing', 'payment_status' => $paymentStatus]);
                    } elseif ($status == 'out_for_delivery') {
                        $updateOrderDetail = OrderDetail::where('id', $detail->id)
                            ->whereIn('delivery_status', ['pending', 'confirmed', 'processing'])
                            ->update(['delivery_status' => 'out_for_delivery', 'payment_status' => $paymentStatus]);
                    } elseif ($status == 'delivered' && !in_array($detail->delivery_status, ['failed', 'returned', 'canceled'])) {
                        $updateOrderDetail = OrderDetail::where('id', $detail->id)
                            ->update(['delivery_status' => 'delivered', 'payment_status' => $paymentStatus]);
                    }
    
                    if ($updateOrderDetail) {
                        $orderDetailUpdated++;
                    }
                }
            }
    
            if ($orderDetailUpdated == 0) {
                DB::rollBack();
                return response()->json(["message" => "Order status could not be updated. Try changing to a more advanced status", "status" => false], 400);
            }
            
            Order::where(['order_group_id' => $groupId])->update(['order_status' => $status]);
            // Commit transaction
            DB::commit();

            $totalOrderPlaced = count($singleOrder['details']);
            if ($totalOrderPlaced != $orderDetailUpdated) {
                return response()->json(["message" => $orderDetailUpdated. " of ".$totalOrderPlaced." Order(s) updated", "status" => true], 200);
            }
            return response()->json(["message" => "Order(s) updated successfully", "status" => true], 200);
        } catch (\Exception $e) {
            // Rollback transaction if something goes wrong
            DB::rollBack();
            return response()->json(["status" => false, "message" => "Server Error: ". $e->getMessage()], 500);
        }
    }

    public function status(Request $request)
    {
        $user_id = auth('admin')->id();

        $order = Order::find($request->id);

        if(!$order->is_guest && !isset($order->customer))
        {
            return response()->json(['customer_status'=>0],200);
        }

        $wallet_status = Helpers::get_business_settings('wallet_status');
        $loyalty_point_status = Helpers::get_business_settings('loyalty_point_status');

        if($request->order_status=='delivered' && $order->payment_status !='paid'){

            return response()->json(['payment_status'=>0],200);
        }
        $fcm_token = isset($order->customer) ? $order->customer->cm_firebase_token : null;
        $value = Helpers::order_status_update_message($request->order_status);
        if(!empty($fcm_token)) {
            try {
                if ($value) {
                    $data = [
                        'title' => translate('Order'),
                        'description' => $value,
                        'order_id' => $order['id'],
                        'image' => '',
                        'type'=>'order'
                    ];
                    Helpers::send_push_notif_to_device($fcm_token, $data);
                }
            } catch (\Exception $e) {
            }
        }

        try {
            $fcm_token_delivery_man = $order->delivery_man->fcm_token;
            if ($request->order_status == 'canceled' && $value != null) {
                $data = [
                    'title' => translate('order'),
                    'description' => $value,
                    'order_id' => $order['id'],
                    'image' => '',
                    'type'=>'order'
                ];
                if($order->delivery_man_id) {
                    self::add_deliveryman_push_notification($data, $order->delivery_man_id);
                }
                Helpers::send_push_notif_to_device($fcm_token_delivery_man, $data);
            }
        } catch (\Exception $e) {
        }

        $order->order_status = $request->order_status;
        OrderManager::stock_update_on_order_status_change($order, $request->order_status);
        $order->save();

        if($loyalty_point_status == 1 && !$order->is_guest)
        {
            if($request->order_status == 'delivered' && $order->payment_status =='paid'){
                CustomerManager::create_loyalty_point_transaction($order->customer_id, $order->id, Convert::default($order->order_amount-$order->shipping_cost), 'order_place');
            }
        }

        $ref_earning_status = BusinessSetting::where('type', 'ref_earning_status')->first()->value ?? 0;
        $ref_earning_exchange_rate = BusinessSetting::where('type', 'ref_earning_exchange_rate')->first()->value ?? 0;

        if(!$order->is_guest && $ref_earning_status == 1 && $request->order_status == 'delivered' && $order->payment_status =='paid'){

            $customer = User::find($order->customer_id);
            $is_first_order = Order::where(['customer_id'=>$order->customer_id,'order_status'=>'delivered','payment_status'=>'paid'])->count();
            $referred_by_user = User::find($customer->referred_by);

            if ($is_first_order == 1 && isset($customer->referred_by) && isset($referred_by_user)){
                CustomerManager::create_wallet_transaction($referred_by_user->id, floatval($ref_earning_exchange_rate), 'add_fund_by_admin', 'earned_by_referral');
            }
        }

        if ($order->delivery_man_id && $request->order_status == 'delivered') {
            $dm_wallet = DeliverymanWallet::where('delivery_man_id', $order->delivery_man_id)->first();
            $cash_in_hand = $order->payment_method == 'cash_on_delivery' ? $order->order_amount : 0;

            if (empty($dm_wallet)) {
                DeliverymanWallet::create([
                    'delivery_man_id' => $order->delivery_man_id,
                    'current_balance' => BackEndHelper::currency_to_usd($order->deliveryman_charge) ?? 0,
                    'cash_in_hand' => BackEndHelper::currency_to_usd($cash_in_hand),
                    'pending_withdraw' => 0,
                    'total_withdraw' => 0,
                ]);
            } else {
                $dm_wallet->current_balance += BackEndHelper::currency_to_usd($order->deliveryman_charge) ?? 0;
                $dm_wallet->cash_in_hand += BackEndHelper::currency_to_usd($cash_in_hand);
                $dm_wallet->save();
            }

            if($order->deliveryman_charge && $request->order_status == 'delivered'){
                DeliveryManTransaction::create([
                    'delivery_man_id' => $order->delivery_man_id,
                    'user_id' => 0,
                    'user_type' => 'admin',
                    'credit' => BackEndHelper::currency_to_usd($order->deliveryman_charge) ?? 0,
                    'transaction_id' => Uuid::uuid4(),
                    'transaction_type' => 'deliveryman_charge'
                ]);
            }
        }

        self::add_order_status_history($request->id, 0, $request->order_status, 'admin');

        $transaction = OrderTransaction::where(['order_id' => $order['id']])->first();
        if (isset($transaction) && $transaction['status'] == 'disburse') {
            return response()->json($request->order_status);
        }

        if ($request->order_status == 'delivered' && $order['seller_id'] != null) {
            OrderManager::wallet_manage_on_order_status_change($order, 'admin');
            OrderDetail::where('order_id', $order->id)->update(
                ['delivery_status'=>'delivered']
            );
        }

        return response()->json($request->order_status);
    }

    public function delivery_date_update(Request $request){
        try {
            $deliveryDate = $request->delivery_date;
            $orderId = $request->order_id;

            $isDateValid = Helpers::isValidAndFutureDate($deliveryDate, 'Y-m-d');
            if (!$isDateValid) {
                return response()->json(["message" => "Invalid delivery date selected. Please select a valid date", "status" => false], 400);
            }

            $order = Order::where(["order_group_id" => $orderId])->first();
            if (!$order) {
                return response()->json(["message" => "Order reference ($orderId) does not exists", "status" => false], 400);
            }
            $user_id = $order->customer_id;
            DB::beginTransaction();
            
            $order->expected_delivery_date = $deliveryDate;
            if ($order->save()) {
                $value = Helpers::order_status_update_message('expected_delivery_date') . " ID: " . $order['id'];
                
                if ($value != null) {
                    $data = [
                        'title' => translate('order'),
                        'description' => $value,
                        'order_id' => $order['id'],
                        'image' => '',
                        'type'=>'order'
                    ];
                    if ($order->delivery_man_id) {
                        self::add_deliveryman_push_notification($data, $order->delivery_man_id);
                    }
                }
                DB::commit();
                return response()->json(['message' => 'Expected delivery dated added. Please ensure order is delivered within stipulated time', 'status' => true], 200);
            }
            DB::rollback();
            return response()->json(['message' => 'Unable to add expected delivery date to order', 'status' => false], 400);
        
        } catch(\Exception $ex){
            DB::rollback();
            return response()->json(['message' => 'Something went wrong processing request', 'status' => false], 400);
        }
    }

    public function amount_date_update(Request $request){
        $field_name = $request->field_name;
        $field_val = $request->field_val;
        $user_id = 0;

        $order = Order::find($request->order_id);
        $order->$field_name = $field_val;

        try {
            DB::beginTransaction();

            if($field_name == 'expected_delivery_date'){
                self::add_expected_delivery_date_history($request->order_id, $user_id, $field_val, 'admin');
            }
            $order->save();

            DB::commit();
        }catch(\Exception $ex){
            DB::rollback();
            return response()->json(['status' => false], 403);
        }

        if($field_name == 'expected_delivery_date') {
            $fcm_token = isset($order->delivery_man) ? $order->delivery_man->fcm_token:null;
            $value = Helpers::order_status_update_message($field_name) . " ID: " . $order['id'];
            if(!empty($fcm_token)) {
                try {
                    if ($value != null) {
                        $data = [
                            'title' => translate('order'),
                            'description' => $value,
                            'order_id' => $order['id'],
                            'image' => '',
                            'type'=>'order'
                        ];

                        if ($order->delivery_man_id) {
                            self::add_deliveryman_push_notification($data, $order->delivery_man_id);
                        }
                        Helpers::send_push_notif_to_device($fcm_token, $data);
                    }
                } catch (\Exception $e) {
                    Toastr::warning(translate('push_notification_failed_for_DeliveryMan'));
                }
            }
        }

        return response()->json(['status' => true], 200);
    }

    public function payment_status(Request $request)
    {
        if ($request->ajax()) {
            $orderId = $request->id;
            $order = Order::find($orderId);
            Log::channel('daily')->info(['payment_status' => $request->payment_status, 'order' => $order]);
            if (!is_null($order)) {
                if($order->is_guest=='0' && !isset($order->customer))
                {
                    return response()->json(['customer_status'=>0],200);
                }
    
                $order = Order::find($orderId);
                $order->payment_status = $request->payment_status;
                $order->save();
                $data = $request->payment_status;
                return response()->json($data);
            } else {
                $order = Order::where(['order_group_id' => $orderId])->get();
                if ($order->count() > 0) {
                    Order::where(['order_group_id' => $orderId])->update(['payment_status' => $request->payment_status]); 
                    return response()->json(['status' => $request->payment_status, 'route' => route('admin.orders.view',['id' => $orderId])]);
                }
                return response()->json($request->payment_status);
            }
        }
    }

    public function generate_invoice($id)
    {
        $company_phone =BusinessSetting::where('type', 'company_phone')->first()->value;
        $company_email =BusinessSetting::where('type', 'company_email')->first()->value;
        $company_name =BusinessSetting::where('type', 'company_name')->first()->value;
        $company_web_logo =BusinessSetting::where('type', 'company_web_logo')->first()->value;

        $order = Order::with('seller')->with('shipping')->with('details')->where('id', $id)->first();
        $seller = Seller::find($order->details->first()->seller_id);
        $data["email"] = $order->customer !=null?$order->customer["email"]:json_decode($order->billing_address_data)->contact_person_name ?? translate('email_not_found');
        $data["client_name"] = $order->customer !=null? $order->customer["f_name"] . ' ' . $order->customer["l_name"]:json_decode($order->billing_address_data)->email ?? translate('customer_not_found');
        $data["order"] = $order;
        $mpdf_view = View::make('admin-views.order.invoice',
            compact('order', 'seller', 'company_phone', 'company_name', 'company_email', 'company_web_logo')
        );
        Helpers::gen_mpdf($mpdf_view, 'order_invoice_', $order->id);
    }

    /*
     *  Digital file upload after sell
     */
    public function digital_file_upload_after_sell(Request $request)
    {
        $request->validate([
            'digital_file_after_sell'    => 'required|mimes:jpg,jpeg,png,gif,zip,pdf'
        ], [
            'digital_file_after_sell.required' => 'Digital file upload after sell is required',
            'digital_file_after_sell.mimes' => 'Digital file upload after sell upload must be a file of type: pdf, zip, jpg, jpeg, png, gif.',
        ]);

        $order_details = OrderDetail::find($request->order_id);
        $order_details->digital_file_after_sell = ImageManager::update('product/digital-product/', $order_details->digital_file_after_sell, $request->digital_file_after_sell->getClientOriginalExtension(), $request->file('digital_file_after_sell'));

        if($order_details->save()){
            Toastr::success(translate('digital_file_upload_successfully'));
        }else{
            Toastr::error(translate('digital_file_upload_failed'));
        }
        return back();
    }

    public function inhouse_order_filter()
    {
        if (session()->has('show_inhouse_orders') && session('show_inhouse_orders') == 1) {
            session()->put('show_inhouse_orders', 0);
        } else {
            session()->put('show_inhouse_orders', 1);
        }
        return back();
    }
    public function update_deliver_info(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->delivery_type = 'third_party_delivery';
        $order->delivery_service_name = $request->delivery_service_name;
        $order->third_party_delivery_tracking_id = $request->third_party_delivery_tracking_id;
        $order->delivery_man_id = null;
        $order->deliveryman_charge = 0;
        $order->expected_delivery_date = null;
        $order->save();

        Toastr::success(translate('updated_successfully'));
        return back();
    }

    public function bulk_export_data(Request $request, $status)
    {
        $search = $request['search'];
        $filter = $request['filter'];
        $from = $request['from'];
        $to = $request['to'];
        $delivery_man_id = $request['delivery_man_id'];

        if ($status != 'all') {
            $orders = Order::when($filter,function($q) use($filter){
                $q->when($filter == 'all', function($q){
                    return $q;
                })
                    ->when($filter == 'POS', function ($q){
                        $q->whereHas('details', function ($q){
                            $q->where('order_type', 'POS');
                        });
                    })
                    ->when($filter == 'admin' || $filter == 'seller', function($q) use($filter){
                        $q->whereHas('details', function ($query) use ($filter){
                            $query->whereHas('product', function ($query) use ($filter){
                                $query->where('added_by', $filter);
                            });
                        });
                    });
            })
                ->with(['customer'])->where(function($query) use ($status){
                    $query->orWhere('order_status',$status)
                        ->orWhere('payment_status',$status);
                });
        } else {
            $orders = Order::with(['customer'])
                ->when($filter,function($q) use($filter){
                    $q->when($filter == 'all', function($q){
                        return $q;
                    })
                        ->when($filter == 'POS', function ($q){
                            $q->whereHas('details', function ($q){
                                $q->where('order_type', 'POS');
                            });
                        })
                        ->when(($filter == 'admin' || $filter == 'seller'), function($q) use($filter){
                            $q->whereHas('details', function ($query) use ($filter){
                                $query->whereHas('product', function ($query) use ($filter){
                                    $query->where('added_by', $filter);
                                });
                            });
                        });
                });
        }

        $key = $request['search'] ? explode(' ', $request['search']) : '';
        $orders = $orders->when($request->has('search') && $search!=null,function ($q) use ($key) {
                $q->where(function($qq) use ($key){
                    foreach ($key as $value) {
                        $qq->where('id', 'like', "%{$value}%")
                            ->orWhere('order_status', 'like', "%{$value}%")
                            ->orWhere('transaction_ref', 'like', "%{$value}%");
                    }});
            })
            ->when($request->has('delivery_man_id') && $delivery_man_id, function($query) use($delivery_man_id){
                $query->where('delivery_man_id', $delivery_man_id);
            })
            ->when(!empty($from) && !empty($to), function($dateQuery) use($from, $to) {
                $dateQuery->whereDate('created_at', '>=',$from)
                    ->whereDate('created_at', '<=',$to);
            })
            ->when($request->seller_id != 'all' && $request->has('seller_id') && $request->seller_id != 0 ,function($query)use($request){
                return $query->where(['seller_is'=>'seller','seller_id'=>$request->seller_id]);
            })
            ->when($request->seller_id != 'all' && $request->has('seller_id') && $request->seller_id == 0 ,function($query)use($request){
                return $query->where(['seller_is'=>'admin']);
            })
            ->when($request->customer_id != 'all' && $request->has('customer_id') ,function($query)use($request){
                return $query->where('customer_id',$request->customer_id);
            })
            ->when($request->has('date_type')&& $request->date_type == "this_year", function($dateQuery) {
                $current_start_year = date('Y-01-01');
                $current_end_year = date('Y-12-31');
                $dateQuery->whereDate('created_at', '>=',$current_start_year)
                    ->whereDate('created_at', '<=',$current_end_year);
            })
            ->when($request->has('date_type')&& $request->date_type == "this_month", function($dateQuery) {
                $current_month_start = date('Y-m-01');
                $current_month_end = date('Y-m-t');
                $dateQuery->whereDate('created_at', '>=',$current_month_start)
                    ->whereDate('created_at', '<=',$current_month_end);
            })
            ->when($request->has('date_type')&& $request->date_type == "this_week", function($dateQuery) {
                $start_week = Carbon::now()->subDays(7)->startOfWeek()->format('Y-m-d');
                $end_week =Carbon::now()->startOfWeek()->format('Y-m-d');
                $dateQuery->whereDate('created_at', '>=',$start_week)
                ->whereDate('created_at', '<=',$end_week );
            })
            ->when($request->has('date_type')&& $request->date_type == "custom_date" && !empty($from) && !empty($to), function($dateQuery) use($from, $to) {
                $dateQuery->whereDate('created_at', '>=',$from)
                    ->whereDate('created_at', '<=',$to);
            })
            ->orderBy('id', 'DESC')->get();

        if ($orders->count()==0) {
            Toastr::warning(translate('data_is_not_available'));
            return back();
        }

        $storage = array();

        foreach ($orders as $item) {

            $order_amount = $item->order_amount;
            $discount_amount = $item->discount_amount;
            $shipping_cost = $item->shipping_cost;
            $extra_discount = $item->extra_discount;

            $storage[] = [
                'order_id'=>$item->id,
                'Customer Id' => $item->customer_id,
                'Customer Name'=> isset($item->customer) ? $item->customer->f_name. ' '.$item->customer->l_name:'not found',
                'Order Group Id' => $item->order_group_id,
                'Order Status' => $item->order_status,
                'Order Amount' => Helpers::currency_converter($order_amount),
                'Order Type' => $item->order_type,
                'Coupon Code' => $item->coupon_code,
                'Discount Amount' => Helpers::currency_converter($discount_amount),
                'Discount Type' => $item->discount_type,
                'Extra Discount' => Helpers::currency_converter($extra_discount),
                'Extra Discount Type' => $item->extra_discount_type,
                'Payment Status' => $item->payment_status,
                'Payment Method' => $item->payment_method,
                'Transaction_ref' => $item->transaction_ref,
                'Verification Code' => $item->verification_code,
                'Billing Address' => isset($item->billingAddress)? $item->billingAddress->address:'not found',
                'Billing Address Data' => $item->billing_address_data,
                'Shipping Type' => $item->shipping_type,
                'Shipping Address' => isset($item->shippingAddress)? $item->shippingAddress->address:'not found',
                'Shipping Method Id' => $item->shipping_method_id,
                'Shipping Method Name' => isset($item->shipping)? $item->shipping->title:'not found',
                'Shipping Cost' => Helpers::currency_converter($shipping_cost),
                'Seller Id' => $item->seller_id,
                'Seller Name' => isset($item->seller)? $item->seller->f_name. ' '.$item->seller->l_name:'not found',
                'Seller Email'  => isset($item->seller)? $item->seller->email:'not found',
                'Seller Phone'  => isset($item->seller)? $item->seller->phone:'not found',
                'Seller Is' => $item->seller_is,
                'Shipping Address Data' => $item->shipping_address_data,
                'Delivery Type' => $item->delivery_type,
                'Delivery Man Id' => $item->delivery_man_id,
                'Delivery Service Name' => $item->delivery_service_name,
                'Third Party Delivery Tracking Id' => $item->third_party_delivery_tracking_id,
                'Checked' => $item->checked,

            ];
        }

        return (new FastExcel($storage))->download('Order_All_details.xlsx');
    }

    /**
     * Update Address From Order Details (Shipping and Billing)
     */
    public function address_update(Request $request){
        $order = $this->order->find($request->order_id);
        $shipping_address_data = json_decode($order->shipping_address_data, true);
        $billing_address_data = json_decode($order->billing_address_data, true);

        $common_address_data = [
            'contact_person_name' => $request->name,
            'phone' => $request->phone_number,
            'city' => $request->city,
            'zip' => $request->zip,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'updated_at' => now(),
        ];

        if ($request->address_type == 'shipping') {
            $shipping_address_data = array_merge($shipping_address_data, $common_address_data);
        } elseif ($request->address_type == 'billing') {
            $billing_address_data = array_merge($billing_address_data, $common_address_data);
        }
        $update_data = [];

        if ($request->address_type == 'shipping') {
            $update_data['shipping_address_data'] = json_encode($shipping_address_data);
        } elseif ($request->address_type == 'billing') {
            $update_data['billing_address_data'] = json_encode($billing_address_data);
        }

        if (!empty($update_data)) {
            DB::table('orders')->where('id', $request->order_id)->update($update_data);
        }
        Toastr::success(translate('successfully_updated'));
        return back();

    }

    public function get_customers(Request $request){
        $key = explode(' ', $request['q']);
        $all_customer = ['id'=>'all','text'=>'All customer'];
        $data = DB::table('users')
            ->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%");
                }
            })
            ->where('id','!=',0)
            ->whereNotNull(['f_name', 'l_name', 'phone'])
            ->limit(20)
            ->get([DB::raw('id,IF(id <> "0", CONCAT(f_name, " ", l_name, " (", phone ,")"),CONCAT(f_name, " ", l_name)) as text')])->toArray();
            array_unshift($data, $all_customer);
        return response()->json($data);

    }

}
