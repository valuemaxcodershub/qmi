<?php

namespace App\Http\Controllers\Seller;

use Exception;
use App\Model\Shop;
use App\CPU\Helpers;
use App\Model\Seller;
use App\Model\Product;
use App\CPU\ImageManager;
use App\CPU\BackEndHelper;
use App\Models\SellerTypes;
use Illuminate\Http\Request;
use App\Model\PaymentRequest;
use App\Models\boosted_products;
use App\Services\UtilityService;
use App\Services\PaystackHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    public function view(Request $request)
    {
        $shop = Shop::where(['seller_id' => auth('seller')->id()])->first();
        if (isset($shop) == false) {
            DB::table('shops')->insert([
                'seller_id' => auth('seller')->id(),
                'name' => auth('seller')->user()->f_name,
                'address' => '',
                'contact' => auth('seller')->user()->phone,
                'image' => 'def.png',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $shop = Shop::where(['seller_id' => auth('seller')->id()])->first();
        }

        $minimum_order_amount= Helpers::get_business_settings('minimum_order_amount_status');
        $minimum_order_amount_by_seller=\App\CPU\Helpers::get_business_settings('minimum_order_amount_by_seller');
        $free_delivery_status= Helpers::get_business_settings('free_delivery_status');
        $free_delivery_responsibility= Helpers::get_business_settings('free_delivery_responsibility');

        if ($request->pagetype == 'order_settings' && (($minimum_order_amount && $minimum_order_amount_by_seller) || ($free_delivery_status && $free_delivery_responsibility == 'seller'))) {
            $seller = Seller::find($shop->seller_id);
            return view('seller-views.shop.order-settings', compact('seller'));
        }

        return view('seller-views.shop.shopInfo', compact('shop'));
    }

    public function edit($id)
    {
        $shop = Shop::where(['seller_id' =>  auth('seller')->id()])->first();
        return view('seller-views.shop.edit', compact('shop'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'banner'      => 'mimes:png,jpg,jpeg|max:2048',
            'image'       => 'mimes:png,jpg,jpeg|max:2048',
        ], [
            'banner.mimes'   => 'Banner image type jpg, jpeg or png',
            'banner.max'     => 'Banner Maximum size 2MB',
            'image.mimes'    => 'Image type jpg, jpeg or png',
            'image.max'      => 'Image Maximum size 2MB',
        ]);

        $shop = Shop::find($id);
        $shop->name = $request->name;
        $shop->address = $request->address;
        $shop->contact = $request->contact;
        if ($request->image) {
            $shop->image = ImageManager::update('shop/', $shop->image, 'png', $request->file('image'));
        }
        if ($request->banner) {
            $shop->banner = ImageManager::update('shop/banner/', $shop->banner, 'png', $request->file('banner'));
        }
        if ($request->bottom_banner) {
            $shop->bottom_banner = ImageManager::update('shop/banner/', $shop->bottom_banner, 'png', $request->file('bottom_banner'));
        }
        // offer Banner For Theme Fashion
        if ($request->offer_banner) {
            $shop->offer_banner = ImageManager::update('shop/banner/', $shop->offer_banner, 'png', $request->file('offer_banner'));
        }
        $shop->save();

        Toastr::info(translate('Shop_updated_successfully'));
        return redirect()->route('seller.shop.view');
    }

    public function vacation_add(Request $request, $id){
        $shop = Shop::find($id);
        $shop->vacation_status = $request->vacation_status == 'on' ? 1 : 0;
        $shop->vacation_start_date = $request->vacation_start_date;
        $shop->vacation_end_date = $request->vacation_end_date;
        $shop->vacation_note = $request->vacation_note;
        $shop->save();

        Toastr::success(translate('Vacation_mode_updated_successfully'));
        return redirect()->back();
    }

    public function temporary_close(Request $request){
        $shop = Shop::find($request->id);

        $shop->temporary_close = $request->get('status', 0);
        $shop->save();

        return response()->json([
            'status' => true,
            'message' => $request->status ? translate("temporary_close_active_successfully") : translate("temporary_close_inactive_successfully"),
        ], 200);
    }

    public function order_settings(Request $request)
    {
        if($request->has('minimum_order_amount')){
            Seller::where('id',auth('seller')->id())->update([
                'minimum_order_amount' => BackEndHelper::currency_to_usd($request->minimum_order_amount),
            ]);
        }

        if($request->has('free_delivery_over_amount')){
            Seller::where('id',auth('seller')->id())->update([
                'free_delivery_status' => $request->free_delivery_status == 'on' ? 1:0,
            ]);
            Seller::where('id',auth('seller')->id())->update([
                'free_delivery_over_amount' => BackEndHelper::currency_to_usd($request->free_delivery_over_amount),
            ]);
        }

        Toastr::success(translate('updated_successfully'));
        return back();
    }

    public function premiumservice() {
        $boostingSettings = Helpers::get_business_settings('product_boosting_setting');
        if ($boostingSettings['allow_boosting'] == 'no') {
            Toastr::error('Boosting of product is currently unavailable');
            return redirect()->route('seller.dashboard.index');
        }
        $boostingFee = 0;
        if ($boostingSettings['allow_boosting'] == 'yes' AND $boostingSettings['charging_mode'] == 'general_mode') {
            $boostingFee = $boostingSettings['boosting_fee'];
        } else {
            $sellerId = auth('seller')->id();
            $sellerData = Seller::where('id', $sellerId)->first();
            $sellerType = SellerTypes::whereId($sellerData['seller_type'])->first();
            $boostingFee = $sellerType->boosting_fee;
        }
        return view('seller-views.shop.premiumservice', compact('boostingFee', 'sellerId'));
    }

    public function searchProduct(Request $request) : JsonResponse {
        $seller_id = $request->seller_id;
        $product_url = $request->product_url;
        if (empty($product_url)) {
            return response()->json(['status' => false, 'message' => 'Please enter product url'], 400);
        }
        if (empty($seller_id)) {
            return response()->json(['status' => false, 'message' => 'Please login to continue'], 401);
        }
        if (filter_var($product_url, FILTER_VALIDATE_URL)) {
            $explodedUrl = explode('/', $product_url);
            $slug = end($explodedUrl);            
        } else {
            $slug = $product_url;
        }
        
        $product = Product::where(['slug' => $slug, 'user_id' => $seller_id])->first();
        if ($product == null) {
            return response()->json(['status' => false, 'message' => 'Product not found'], 400);
        }
        return response()->json(['status' => true, 'message' => 'Product found', 'data' => [
                'id' => $product->id, 
                'name' => $product->name, 
                'slug' => $product->slug, 
                'image' => json_decode($product->images, true)[0]
            ]
        ], 200);
    }

    public function  boostProduct(Request $request)  {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|numeric',
            'days' => 'required|numeric|min:1',
            'product_url' => 'required|string',
            'payment_method' => 'required|string',
        ]);
        if ($validator->fails()) {
            Toastr::error($validator->errors()->first());
            return redirect()->back();
        }
        $data = $validator->validated();
        $product_id = $data['product_id'];
        $days = $data['days'];
        $payment_method = $data['payment_method'];
        
        $isProductAlreadyBoosted = boosted_products::where(['product_id' => $product_id, 'seller_id' => auth('seller')->id(), 'status' => 'active'])->first();
        if (!empty($isProductAlreadyBoosted)) {
            Toastr::error('Product is already boosted for more engagements');
            return redirect()->back();
        }

        $boostingSettings = Helpers::get_business_settings('product_boosting_setting');
        $sellerId = auth('seller')->id();
        $sellerData = Seller::with('sellertype')->where('id', $sellerId)->first();
        if (str_contains(strtolower($sellerData->sellertype->name), 'free')) {
            Toastr::error('Boosting of product is only available for verified sellers');
            return redirect()->back();
        }

        if ($boostingSettings['allow_boosting'] == 'yes' AND $boostingSettings['charging_mode'] == 'general_mode') {
            $boostingFee = $boostingSettings['boosting_fee'];
        } else {
            $sellerType = SellerTypes::whereId($sellerData['seller_type'])->first();
            $boostingFee = $sellerType->boosting_fee;
        }
        $amount_to_pay = $boostingFee * $days;
        $productInfo = Product::where(['id' => $product_id, 'user_id' => $sellerId])->first();
        $paymentReference = app(UtilityService::class)->uniqueReference();

        $isPaystackActive = DB::table('addon_settings')->where(['key_name' => 'paystack', 'settings_type' => 'payment_config', 'is_active' => '1'])->first();
        if ($isPaystackActive == null) {
            Toastr::error('Payment gateway is not enabled. Please contact Admin');
            return redirect()->back();
        }

        boosted_products::create([
            'product_id' => $product_id,
            'seller_id' => $sellerId,
            'days' => $days,
            'price' => $boostingFee,
            'amount_to_bill' => $amount_to_pay,
            'reference' => $paymentReference,
            'status' => 'pending',
         ]);
 
        PaymentRequest::create([
            'payer_id' => $sellerId,
            'payment_amount' => $amount_to_pay,
            'reference' => $paymentReference,
            'currency_code' => 'NGN',
            'payment_method' => 'paystack',
            'payment_platform' => 'web',
            'attribute' => 'product-boosting',
            'payer_information' => json_encode([
                 'name' => $sellerData->f_name . ' '. $sellerData->l_name,
                 'phone' => $sellerData->phone,
                 'email' => $sellerData->email,
             ])
        ]);
         
        $paystack = new PaystackHandler(new PaymentRequest, new Seller);
        $getLink = $paystack->generatePaymentLink($paymentReference, 'seller');
        
        if($getLink !== false) {
            return redirect($getLink);
        }
        Toastr::error('Something went wrong internally. Contact Administrator');
        return redirect()->back();
    }

    public function approveBoostingPaymentOnline(Request $request, string $processorType) {
        $validator = Validator::make($request->all(), [
            "status" => "string",
            "tx_ref" => "numeric|required",
            "trxref" => "numeric|required",
            "reference" => "numeric|required",
            "transaction_id" => "numeric|sometimes"
        ]);
        $data = $validator->validated();
        $reference = $data['reference'];
        $trxref = $data['trxref'];
        $sellerId = auth('seller')->id();
        
        $todayDate = Carbon::today();

        $paystack = new PaystackHandler(new PaymentRequest, new Seller);
        $verifyPayment = $paystack->verifyPayment($reference);
        if ($verifyPayment) {            
            $boostProduct = boosted_products::where(['reference' => $reference, 'seller_id' => $sellerId])->first();

            if ($boostProduct) {
                $boostProduct->status = 'active';
                $boostProduct->expiry_date = $todayDate->addDays((int) $boostProduct->days);
                if ($boostProduct->save()) {
                    PaymentRequest::where(['reference' => $reference, 'payer_id' => $sellerId])->update(['is_paid' => '1']);
                    Toastr::success('Payment approved successfully. The product has been boosted.');
                    return redirect()->route('seller.shop.premiumservice');
                } else {
                    Toastr::success('Payment was successful. Unable to approve product for boosting at this time. Contact Administrator');
                    return redirect()->route('seller.shop.premiumservice');
                }                
            } else {
                Toastr::error('Payment failed. Please contact administrator if you were debited from your account');
                return redirect()->route('seller.shop.premiumservice');
            }
        } else {
            Toastr::error('Payment failed. Please contact administrator if you were debited from your account');
            return redirect()->route('seller.shop.premiumservice');
        }
    }

}
