<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\CPU\Helpers;
use App\Model\Order;
use App\Model\Review;
use App\Model\Seller;
use App\Model\Product;
use App\CPU\BackEndHelper;
use App\Model\DeliveryMan;
use App\CPU\ProductManager;
use App\Model\SellerWallet;
use App\Models\SellerTypes;
use App\Traits\CommonTrait;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Model\BusinessSetting;
use App\Model\DeliveryZipCode;
use App\Model\ShippingAddress;
use App\Model\WithdrawRequest;
use App\Model\OrderTransaction;
use App\Services\UtilityService;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SellerController extends Controller
{
    use CommonTrait;
    public function __construct(
        private DeliveryZipCode $delivery_zip_code,
        private Seller $seller,
    ){

    }
    public function index(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        $current_date = date('Y-m-d');

        $sellers = Seller::with(['orders', 'product'])
            ->when($search, function($query) use($search){
                $key = explode(' ', $search);
                foreach ($key as $value) {
                    $query->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%");
                }
            })
            ->latest()
            ->paginate(Helpers::pagination_limit())
            ->appends($query_param);

        return view('admin-views.seller.index', compact('sellers', 'search', 'current_date'));
    }

    public function view(Request $request, $id, $tab = null)
    {
        $seller = $this->seller->with(['product'])->withCount('orders','product')->where('id',$id)->first();
        // return $seller;
        /**
         * for seller rating
         */
            $seller?->product?->map(function($product){
                $product['rating'] = $product?->reviews->pluck('rating')->sum();
                $product['rating_count'] = $product->reviews->count();

                $product['single_rating_5'] = 0;
                $product['single_rating_4'] = 0;
                $product['single_rating_3'] = 0;
                $product['single_rating_2'] = 0;
                $product['single_rating_1'] = 0;
                foreach($product->reviews as $review)
                {
                    $rating = $review->rating;
                    match ($rating) {
                        5 => $product->single_rating_5++,
                        4 => $product->single_rating_4++,
                        3 => $product->single_rating_3++,
                        2 => $product->single_rating_2++,
                        1 => $product->single_rating_1++,
                    };
                }

            });
            $seller['single_rating_5'] = $seller?->product->pluck('single_rating_5')->sum();
            $seller['single_rating_4'] = $seller?->product->pluck('single_rating_4')->sum();
            $seller['single_rating_3'] = $seller?->product->pluck('single_rating_3')->sum();
            $seller['single_rating_2'] = $seller?->product->pluck('single_rating_2')->sum();
            $seller['single_rating_1'] = $seller?->product->pluck('single_rating_1')->sum();
            $seller['total_rating'] = $seller?->product->pluck('rating')->sum();
            $seller['rating_count'] = $seller->product->pluck('rating_count')->sum();
            $seller['average_rating'] = $seller['total_rating'] / ($seller['rating_count'] == 0 ? 1 : $seller['rating_count']);
         /**
         * End for seller rating
         */
        if(!isset($seller))
        {
            Toastr::error(translate('seller_not_found_It_may_be_deleted'));
            return back();
        }
        $current_date = date('Y-m-d');

        if ($tab == 'order') {
            $id = $seller->id;
            $orders = Order::where(['seller_is'=>'seller'])->where(['seller_id'=>$id])->where('order_type','default_type')->latest()->paginate(Helpers::pagination_limit());

            return view('admin-views.seller.view.order', compact('seller', 'orders'));
        } else if ($tab == 'product') {
            $products = Product::where('added_by', 'seller')->where('user_id', $seller->id)->latest()->paginate(Helpers::pagination_limit());
            return view('admin-views.seller.view.product', compact('seller', 'products'));
        } else if ($tab == 'setting') {
            $commission = $request['commission'];
            if ($request->has('commission')) {
                request()->validate([
                    'commission' => 'required | numeric | min:1',
                ]);

                if ($request['commission_status'] == 1 && $request['commission'] == null) {
                    Toastr::error(translate('you_did_not_set_commission_percentage_field.'));
                    //return back();
                } else {
                    $seller = Seller::find($id);
                    $seller->sales_commission_percentage = $request['commission_status'] == 1 ? $request['commission'] : null;
                    $seller->save();

                    Toastr::success(translate('commission_percentage_for_this_seller_has_been_updated.'));
                }
            }
            $commission = 0;
            if ($request->has('gst')) {
                if ($request['gst_status'] == 1 && $request['gst'] == null) {
                    Toastr::error(translate('you_did_not_set_GST_number_field.'));
                    //return back();
                } else {
                    $seller = Seller::find($id);
                    $seller->gst = $request['gst_status'] == 1 ? $request['gst'] : null;
                    $seller->save();

                    Toastr::success(translate('GST_number_for_this_seller_has_been_updated.'));
                }
            }
            if ($request->has('seller_pos_update')) {
                    $seller = Seller::find($id);
                    $seller->pos_status = $request->get('seller_pos', 0);
                    $seller->save();

                    Toastr::success(translate('seller_pos_permission_updated.'));
            }

            //return back();
            return view('admin-views.seller.view.setting', compact('seller'));
        } else if ($tab == 'transaction') {
            $transactions = OrderTransaction::with('order.customer')->where('seller_is','seller')->where('seller_id',$seller->id);

            $query_param = [];
            $search = $request['search'];
            if ($request->has('search'))
            {
                $key = explode(' ', $request['search']);
                $transactions = $transactions->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->Where('order_id', 'like', "%{$value}%")
                            ->orWhere('transaction_id', 'like', "%{$value}%");
                    }
                });
                $query_param = ['search' => $request['search']];
            }else{
                $transactions = $transactions;
            }
            $status = $request['status'];
            if ($request->has('status') && $status!='all')
            {
                $key = explode(' ', $request['status']);
                $transactions = $transactions->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->Where('status', 'like', "%{$value}%");
                    }
                });
                $query_param = ['status' => $request['status']];
            }
               $transactions = $transactions->latest()->paginate(Helpers::pagination_limit())->appends($query_param);

            return view('admin-views.seller.view.transaction', compact('seller', 'transactions','search','status'));

        } else if ($tab == 'review') {
            $sellerId = $seller->id;

            $query_param = [];
            $search = $request['search'];
            if ($request->has('search')) {
                $key = explode(' ', $request['search']);
                $product_id = Product::where('added_by','seller')->where('user_id',$sellerId)->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->where('name', 'like', "%{$value}%");
                    }
                })->pluck('id')->toArray();

                $reviews = Review::with(['product'])
                    ->whereIn('product_id',$product_id);

                $query_param = ['search' => $request['search']];
            } else {
                $reviews = Review::with(['product'])->whereHas('product', function ($query) use ($sellerId) {
                    $query->where('user_id', $sellerId)->where('added_by', 'seller');
                });
            }
            //dd($reviews->count());
            $reviews = $reviews->latest()->paginate(Helpers::pagination_limit())->appends($query_param);

            return view('admin-views.seller.view.review', compact('seller', 'reviews', 'search'));
        }
        return view('admin-views.seller.view', compact('seller','current_date'));
    }

    public function updateStatus(Request $request)
    {
        $seller = Seller::findOrFail($request->id);
        $seller->status = $request->status;
        if ($request->status == "approved") {
            Toastr::success(translate('Seller_has_been_approved_successfully'));
        } else if ($request->status == "rejected") {
            Toastr::info(translate('Seller_has_been_rejected_successfully'));
        } else if ($request->status == "suspended") {
            $seller->auth_token = Str::random(80);
            Toastr::info(translate('Seller_has_been_suspended_successfully'));
        }
        $seller->save();
        return back();
    }

    public function order_list($seller_id)
    {
        $orders = Order::where(['seller_id'=> $seller_id, 'seller_is'=> 'seller'])
                ->latest()
                ->paginate(Helpers::pagination_limit());

        $seller = Seller::findOrFail($seller_id);
        return view('admin-views.seller.order-list', compact('orders', 'seller'));
    }

    public function product_list($seller_id)
    {
        $product = Product::where(['user_id' => $seller_id, 'added_by' => 'seller'])->latest()->paginate(Helpers::pagination_limit());
        $seller = Seller::findOrFail($seller_id);
        return view('admin-views.seller.porduct-list', compact('product', 'seller'));
    }

    public function order_details($order_id, $seller_id)
    {
        $country_restrict_status = Helpers::get_business_settings('delivery_country_restriction');
        $zip_restrict_status = Helpers::get_business_settings('delivery_zip_code_area_restriction');
        $countries = $country_restrict_status ? $this->get_delivery_country_array() : COUNTRIES;
        $zip_codes = $zip_restrict_status ? $this->delivery_zip_code->all() : 0;

        $order = Order::with(['shipping','customer'])->where(['id' => $order_id])->first();

        $physical_product = false;
        foreach($order->details as $product){
            if(isset($product->product) && $product->product->product_type == 'physical'){
                $physical_product = true;
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

        $shipping_address = ShippingAddress::find($order->shipping_address);
        $total_delivered = Order::where(['seller_id' => $order->seller_id, 'order_status' => 'delivered', 'order_type' => 'default_type'])->count();

        $linked_orders = Order::where(['order_group_id' => $order['order_group_id']])
            ->whereNotIn('order_group_id', ['def-order-group'])
            ->whereNotIn('id', [$order['id']])
            ->get();


        return view('admin-views.seller.order-details', compact('order', 'seller_id','delivery_men','linked_orders','physical_product',
            'shipping_address','total_delivered', 'countries','zip_codes','zip_restrict_status','country_restrict_status'));
    }

    public function withdraw()
    {
        $all = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'all' ? 1 : 0;
        $active = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'approved' ? 1 : 0;
        $denied = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'denied' ? 1 : 0;
        $pending = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'pending' ? 1 : 0;

        $withdraw_req = WithdrawRequest::with(['seller'])->whereNotNull('seller_id')
            ->when($all, function ($query) {
                return $query;
            })
            ->when($active, function ($query) {
                return $query->where('approved', 1);
            })
            ->when($denied, function ($query) {
                return $query->where('approved', 2);
            })
            ->when($pending, function ($query) {
                return $query->where('approved', 0);
            })
            ->orderBy('id', 'desc')
            ->latest()
            ->paginate(Helpers::pagination_limit());

        return view('admin-views.seller.withdraw', compact('withdraw_req'));
    }

    public function customerWithdraw()
    {
        $all = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'all' ? 1 : 0;
        $active = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'approved' ? 1 : 0;
        $denied = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'denied' ? 1 : 0;
        $pending = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'pending' ? 1 : 0;

        $withdraw_req = WithdrawRequest::with(['user'])->whereNotNull('user_id')
            ->when($all, function ($query) {
                return $query;
            })
            ->when($active, function ($query) {
                return $query->where('approved', 1);
            })
            ->when($denied, function ($query) {
                return $query->where('approved', 2);
            })
            ->when($pending, function ($query) {
                return $query->where('approved', 0);
            })
            ->orderBy('id', 'desc')
            ->latest()
            ->paginate(Helpers::pagination_limit());

            // return $withdraw_req; 

        return view('admin-views.customer.withdraw', compact('withdraw_req'));
    }

    public function withdraw_list_export_excel(Request $request){
        $all = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'all' ? 1 : 0;
        $active = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'approved' ? 1 : 0;
        $denied = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'denied' ? 1 : 0;
        $pending = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'pending' ? 1 : 0;

        $withdraw_requests = WithdrawRequest::with(['seller', 'withdraw_method'])
            ->whereNull('delivery_man_id')
            ->when($all, function ($query) {
                return $query;
            })
            ->when($active, function ($query) {
                return $query->where('approved', 1);
            })
            ->when($denied, function ($query) {
                return $query->where('approved', 2);
            })
            ->when($pending, function ($query) {
                return $query->where('approved', 0);
            })
            ->orderBy('id', 'desc')->get();

        $withdraw_requests->map(function ($query) {
            //company info
            $query->shop_name = isset($query->seller) ? $query->seller->shop->name : '';
            $query->shop_phone = isset($query->seller) ? $query->seller->shop->contact : '';
            $query->shop_address = isset($query->seller) ? $query->seller->shop->address : '';
            $query->shop_email = isset($query->seller) ? $query->seller->email : '';

            $query->withdrawal_amount = BackEndHelper::set_symbol(BackEndHelper::usd_to_currency($query->amount));
            $query->status = $query->approved == 0 ? 'Pending' : ($query->approved == 1 ? 'Approved':'Denied');
            $query->note = $query->transaction_note;

            //method info
            $query->withdraw_method_name = isset($query->withdraw_method) ? $query->withdraw_method->method_name : '';
            if(!empty($query->withdrawal_method_fields)){
                foreach (json_decode($query->withdrawal_method_fields) as $key=>$field) {
                    $query[$key] = $field;
                }
            }
        });

        foreach ($withdraw_requests as $key=>$item) {
            unset($item['id']);
            unset($item['seller_id']);
            unset($item['admin_id']);
            unset($item['delivery_man_id']);
            unset($item['request_updated_by']);
            unset($item['created_at']);
            unset($item['updated_at']);
            unset($item['amount']);
            unset($item['approved']);
            unset($item['withdrawal_method_fields']);
            unset($item['withdrawal_method_id']);
            unset($item['withdraw_id']);
            unset($item['transaction_note']);
            unset($item['provider']);
            unset($item['withdraw_method']);
        }
        return (new FastExcel($withdraw_requests))->download(time() . '-file.xlsx');
    }

    public function withdraw_view($withdraw_id, $seller_id)
    {

        $withdraw_request = WithdrawRequest::with([
            'user',
            'seller',
        ])->when($seller_id, function ($query) use ($seller_id) {
            return $query->where(['id' => $seller_id]);
        })->when($seller_id, function ($query) use ($seller_id) {
            return $query->where(['id' => $seller_id]);
        })->first();

        
        $withdrawal_method = json_decode($withdraw_request->withdrawal_method_fields);

        $withdraw_request->statusHtml = app(UtilityService::class)->statusBadge($withdraw_request->approved);
        
        if (isset($withdraw_request->user)) {
            return view('admin-views.seller.withdraw-view', compact('withdraw_request', 'withdrawal_method'));
        } else {
            return view('admin-views.seller.withdraw-view', compact('withdraw_request', 'withdrawal_method'));
        }
    }

    public function withdrawStatus(Request $request, $id)
    {
        $withdraw = WithdrawRequest::find($id);
        $withdraw->approved = $request->approved;
        $withdraw->transaction_note = $request['note'];
        if ($request->approved == 1) {
            SellerWallet::where('seller_id', $withdraw->seller_id)->increment('withdrawn', $withdraw['amount']);
            SellerWallet::where('seller_id', $withdraw->seller_id)->decrement('pending_withdraw', $withdraw['amount']);
            $withdraw->save();
            Toastr::success(translate('Seller_Payment_has_been_approved_successfully'));
            return redirect()->route('admin.sellers.withdraw_list');
        }

        SellerWallet::where('seller_id', $withdraw->seller_id)->increment('total_earning', $withdraw['amount']);
        SellerWallet::where('seller_id', $withdraw->seller_id)->decrement('pending_withdraw', $withdraw['amount']);
        $withdraw->save();
        Toastr::info(translate('Seller_Payment_request_has_been_Denied_successfully'));
        return redirect()->route('admin.sellers.withdraw_list');

    }

    public function sales_commission_update(Request $request, $id)
    {
        if ($request['status'] == 1 && $request['commission'] == null) {
            Toastr::error(translate('you_did_not_set_commission_percentage_field.'));
            return back();
        }

        $seller = Seller::find($id);
        $seller->sales_commission_percentage = $request['status'] == 1 ? $request['commission'] : null;
        $seller->save();

        Toastr::success(translate('Commission_percentage_for_this_seller_has_been_updated.'));
        return back();
    }
    public function add_seller()
    {
        return view('admin-views.seller.add-new-seller');
    }

    public function sellerTypes() {
        $sellerTypes = SellerTypes::all();
        return view('admin-views.seller.sellertype', compact('sellerTypes'));
    }

    public function addSellerTypesView() {
        $sellerTypes = SellerTypes::all();
        return view('admin-views.seller.sellertype_add', compact('sellerTypes'));
    }

    public function addSellerTypes(Request $request) {
        $validator = Validator::make($request->all(), [
            'seller_type' => 'string|required',
            'amount' => 'numeric|required',
            'boosting_fee' => 'numeric|required',
            'seller_product_limit' => 'numeric|required',
            'seller_rank_color' => 'string|required',
            'allowedPackages.*' => 'string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $sellerType = $data['seller_type'];
        
        $isExist = SellerTypes::where('name', $sellerType)->first();

        if ($isExist != NULL) {
            Session::flash('error', 'Seller type already exists');
            return redirect()->back()->withInput();
        }

        $createType = SellerTypes::create([
            'name' => $sellerType,
            'amount' => $data['amount'],
            'boosting_fee' => $data['boosting_fee'],
            'product_limit' => $data['seller_product_limit'],
            'rank_color' => strtolower($data['seller_rank_color']),
            'allowed_packages' => isset($data['allowedPackages']) ? strtolower(implode(',', $data['allowedPackages'])) : NULL,
        ]);

        if(!$createType) {
            Session::flash('error', 'Error processing request');
            return redirect()->back()->withInput();
        }
        Session::flash('success', 'Seller type created successfully');
        return redirect()->route('admin.sellers.seller-types');        
    }

    public function deleteSellerTypes(Request $request, $id) {
        $checkSeller = Seller::where('seller_type', $id)->count();
        if ($checkSeller > 0) {
            Session::flash('error', "You cant delete this package because there are active sellers on this package");
            return redirect()->route('admin.sellers.seller-types');
        }
        $deleteTypes = SellerTypes::where('id', $id)->delete();
        if (!$deleteTypes) {
            Session::flash('error', "Error deleting seller types");
            return redirect()->route('admin.sellers.seller-types');
        }
        Session::flash('success', 'Seller type deleted successfully');
        return redirect()->route('admin.sellers.seller-types');   
    }

    public function sellerTypes_editView(Request $request, $id) {
        $typeInfo = SellerTypes::where('id', $id)->first();
        $sellerTypes = SellerTypes::all();
        return view('admin-views.seller.sellertype-edit', compact('sellerTypes', 'typeInfo'));
    }

    public function updateSellerType(Request $request) {
        $validator = Validator::make($request->all(), [
            'seller_type' => 'string|required',
            'amount' => 'numeric|required',
            'boosting_fee' => 'numeric|required',
            'seller_product_limit' => 'numeric|required',
            'seller_rank_color' => 'string|required',
            'allowedPackages.*' => 'string',
            'type_id' => 'numeric|required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        
        $data = $validator->validated();
        $updateType = SellerTypes::where('id', $data['type_id'])->update([
            'name' => $data['seller_type'],
            'amount' => $data['amount'],
            'boosting_fee' => $data['boosting_fee'],
            'product_limit' => $data['seller_product_limit'],
            'rank_color' => $data['seller_rank_color'],
            'allowed_packages' => isset($data['allowedPackages']) ? strtolower(implode(',', $data['allowedPackages'])) : NULL,
        ]);

        if(!$updateType) {
            Session::flash('error', 'Error processing request');
            return redirect()->back();
        }
        Session::flash('success', 'Seller type updated successfully');
        return redirect()->route('admin.sellers.seller-types'); 
    }

    public function sellerTypeSettingsView() {
        $sellerTypes = SellerTypes::all();
        $defaultSettings = BusinessSetting::where('type', 'default_seller_type')->first();
        // return [$sellerTypes , $defaultSettings];
        return view('admin-views.seller.sellertype-settings', compact('sellerTypes', 'defaultSettings'));
    }

    public function updateDefaultSellerType(Request $request) {
        $validator = Validator::make($request->all(), [
            'defaultTypeId' => 'numeric|required',
        ]);
        $data = $validator->validated();
        $defaultId = $data['defaultTypeId'];
        $updateType = BusinessSetting::where('type', 'default_seller_type')->update([
            'value' => $defaultId
        ]);

        if(!$updateType) {
            Session::flash('error', 'Error processing request');
            return redirect()->back();
        }
        Seller::whereNull('seller_type')->update([
            'seller_type' => $defaultId
        ]);
        Session::flash('success', 'Default Seller type updated successfully');
        return redirect()->route('admin.sellers.seller-types-settings'); 
    }

}