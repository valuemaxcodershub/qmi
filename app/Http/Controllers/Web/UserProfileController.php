<?php

namespace App\Http\Controllers\Web;

use Exception;
use Carbon\Carbon;
use App\CPU\Convert;
use App\CPU\Helpers;
use App\Model\Order;
use App\Models\User;
use App\Model\Coupon;
use App\Model\Review;
use App\Model\Seller;
use App\Model\Product;
use App\Model\Wishlist;
use App\CPU\ImageManager;
use App\CPU\OrderManager;
use App\Model\AdminWallet;
use App\Model\DeliveryMan;
use App\Model\OrderDetail;
use App\Model\SellerWallet;
use App\Models\PayoutBanks;
use App\Traits\CommonTrait;
use App\CPU\CustomerManager;
use App\Model\RefundRequest;
use App\Model\SupportTicket;
use Illuminate\Http\Request;
use App\Model\PaymentRequest;
use App\Model\ProductCompare;
use App\Model\DeliveryZipCode;
use App\Model\ShippingAddress;
use function App\CPU\translate;
use function React\Promise\all;
use App\Model\DeliveryCountryCode;

use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Model\WalletTransaction;
use App\Services\FlutterwaveHandler;
use Brian2694\Toastr\Facades\Toastr;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    use CommonTrait;

    public function __construct(
        private Order $order,
        private Seller $seller,
        private Product $product,
        private Review $review,
        private DeliveryMan $deliver_man,
        private ProductCompare $compare,
        private Wishlist $wishlist,
    )
    {

    }

    public function user_profile(Request $request)
    {
        $wishlists = $this->wishlist->whereHas('wishlistProduct', function ($q) {
            return $q;
        })->where('customer_id', auth('customer')->id())->count();
        $total_order = $this->order->where('customer_id', auth('customer')->id())->count();
        $total_loyalty_point = auth('customer')->user()->loyalty_point;
        $total_wallet_balance = auth('customer')->user()->wallet_balance;
        $addresses = ShippingAddress::where('customer_id', auth('customer')->id())->get();
        $customer_detail = User::where('id', auth('customer')->id())->first();
        
        if ($customer_detail['transact_pin'] == '000000') {
            Session::flash('error', 'You are yet to set up your transaction pin, kindly update now');
            return redirect()->route('user-transact-pin');
        }

        $bankDetail = $customer_detail['bank_detail'] != NULL ? json_decode($customer_detail['bank_detail'], true) : NULL;

        if ($bankDetail == NULL) {
            Session::flash('error', 'You are yet to set up your banking information, kindly update now');
            return redirect()->route('user-bank-account');
        }

        if (count($addresses) < 1) {
            Session::flash('error', 'You are yet to set up a delivery address on your account. Kindly update now');
            return redirect()->route('account-address-add');
        }

        return view(VIEW_FILE_NAMES['user_profile'], compact('customer_detail', 'addresses', 'wishlists', 'total_order', 
                                        'total_loyalty_point', 'total_wallet_balance', 'bankDetail'));
    }

    public function user_account(Request $request)
    {
        $country_restrict_status = Helpers::get_business_settings('delivery_country_restriction');
        $customerDetail = User::where('id', auth('customer')->id())->first();
        $bankDetail = $customerDetail['bank_detail'] != NULL ? json_decode($customerDetail['bank_detail'], true) : NULL;
        return view(VIEW_FILE_NAMES['user_account'], compact('customerDetail', 'bankDetail'));
    }

    public function bankAccountView(Request $request)
    {
        $customerDetail = User::where('id', auth('customer')->id())->first();
        $bankDetail = $customerDetail['bank_detail'] != NULL ? json_decode($customerDetail['bank_detail'], true) : NULL;
        $allBanks = PayoutBanks::orderBy('bank_name' , 'asc')->get();
        
        return view(VIEW_FILE_NAMES['user_bank_account'], compact('customerDetail', 'bankDetail', 'allBanks'));
    }

    public function updateBankAccount(Request $request) {
        $validator = Validator::make($request->all(), [
            'bank' => 'numeric|required',
            'account_number' => 'numeric|required',
            'transact_pin' => ["numeric", "required", "not_in:000000"],
        ], [
            'bank.required' => 'Bank selection is required',
            'account_number.required' => 'Account number is required',
            'transact_pin.required' => 'Transaction pin is required',
            'transact_pin.not_in' => 'Default transaction pin (000000) cannot be used in transacting.',
        ]);
        
        if($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }     
        
        $bankData = $validator->validated();
        $bankCode = $bankData['bank'];
        $accountNumber = $bankData['account_number'];
        $transactPin = $bankData['transact_pin']; 
        
        $customerDetail = User::where('id', auth('customer')->id())->first();

        if ($transactPin != $customerDetail['transact_pin']) {
            Session::flash('error', 'Incorrect current pin supplied');
            return redirect()->back();
        } 
        
        $getBank = PayoutBanks::where('bank_code', $bankCode)->first();

        $flutterwave = new FlutterwaveHandler(new PaymentRequest, new User);
        $decodeResponse = json_decode((string) $flutterwave->verifyBankAccount($bankCode, $accountNumber), true);

       if ($decodeResponse['status'] == 'error') {
            Session::flash('error', 'Incorrect bank account supplied or wrong selected bank was choosen');
            return redirect()->back();
        }

        $updateBank = User::where(['id' => auth('customer')->id()])->update([
            'bank_detail' => json_encode([
                'bank' => $getBank->bank_name,
                'bank_code' => $bankCode,
                'account_name' => $decodeResponse['data']['account_name'],
                'account_number' => $accountNumber
            ])
        ]);

        if (!$updateBank) {
            Session::flash('error', 'Error updating bank, please try again');
            return redirect()->back();
        }
        
        Session::flash('success', 'Banking information updated successfully');
        return redirect()->back();
    }

    public function user_update(Request $request)
    {
        $request->validate([
            'f_name' => 'required',
            'l_name' => 'required'
        ], [
            'f_name.required' => 'First name is required',
            'l_name.required' => 'Last name is required',
        ]);
        if ($request->password) {
            $request->validate([
                'password' => 'required|min:8|same:confirm_password'
            ]);
        }

        $image = $request->file('image');

        if ($image != null) {
            $imageName = ImageManager::update('profile/', auth('customer')->user()->image, 'png', $request->file('image'));
        } else {
            $imageName = auth('customer')->user()->image;
        }

        User::where('id', auth('customer')->id())->update([
            'image' => $imageName,
        ]);

        $userDetails = [
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'phone' => $request->phone,
            'password' => strlen($request->password) > 5 ? bcrypt($request->password) : auth('customer')->user()->password
        ];
        if (auth('customer')->check()) {
            User::where(['id' => auth('customer')->id()])->update($userDetails);
            Toastr::info(translate('updated_successfully'));
            return redirect()->back();
        } else {
            return redirect()->back();
        }
    }

    public function transactPinView() {
        $customerDetail = User::where('id', auth('customer')->id())->first();
        return view(VIEW_FILE_NAMES['transact_pin_view'], compact('customerDetail'));
    }

    public function updateTransactPin(Request $request) {
        $validator = Validator::make($request->all(), [
            'current_pin' => 'numeric|required',
            'new_pin' => [
                'numeric', 'required',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value == $request->input('current_pin')) {
                        $fail('The new pin cannot be the same as the current pin.');
                    }
                    if ($value == '000000') {
                        $fail('Default pin of "000000" cannot be used as security pin.');
                    }
                },
                'same:retype_pin',
            ],
        ], [
            'current_pin.required' => 'Current Pin is required',
            'new_pin.required' => 'New Pin is required',
            'retype_pin.required' => 'Please retype your new pin',
            'new_pin.same' => 'Pin mismatch',
            'new_pin.numeric' => 'New Pin must be numeric value',
            'new_pin.max' => 'New Pin cannot be more than 6 digits',
        ]);
        
        if($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $securityData = $validator->validated();
        $customerDetail = User::where('id', auth('customer')->id())->first();
        
        if (strlen($securityData['new_pin']) > 6 OR strlen($securityData['new_pin']) < 6) {
            Session::flash('error', 'New pin cannot exceed or more than 6 digits ');
            return redirect()->back();
        }
        
        if ($customerDetail['transact_pin'] != $securityData['current_pin']) {
            Session::flash('error', 'Incorrect current pin supplied');
            return redirect()->back();
        }

        $updatePin = User::where('id', auth('customer')->id())->update([
            'transact_pin' => $securityData['new_pin']
        ]);

        if (!$updatePin) {
            Session::flash('error', 'Request failed. Please try again');
            return redirect()->back();
        }
        Session::flash('success', 'Security pin updated successfully');
        return redirect()->back();
    }

    public function account_address_add()
    {
        $country_restrict_status = Helpers::get_business_settings('delivery_country_restriction');
        $zip_restrict_status = Helpers::get_business_settings('delivery_zip_code_area_restriction');
        $default_location = Helpers::get_business_settings('default_location');

        $countries = $country_restrict_status ? $this->get_delivery_country_array() : COUNTRIES;

        $zip_codes = $zip_restrict_status ? DeliveryZipCode::all() : 0;

        return view(VIEW_FILE_NAMES['account_address_add'], compact('countries', 'zip_restrict_status', 'zip_codes', 'default_location'));
    }

    public function account_delete($id)
    {
        if (auth('customer')->id() == $id) {
            $user = User::find($id);

            $ongoing = ['out_for_delivery','processing','confirmed', 'pending'];
            $order = Order::where('customer_id', $user->id)->whereIn('order_status', $ongoing)->count();
            if($order>0){
                Toastr::warning(translate('you_can`t_delete_account_due_ongoing_order'));
                return redirect()->back();
            }
            auth()->guard('customer')->logout();

            ImageManager::delete('/profile/' . $user['image']);
            session()->forget('wish_list');

            $user->delete();
            Toastr::info(translate('Your_account_deleted_successfully!!'));
            return redirect()->route('home');
        } else {
            Toastr::warning(translate('access_denied').'!!');
        }

    }

    public function account_address()
    {
        $country_restrict_status = Helpers::get_business_settings('delivery_country_restriction');
        $zip_restrict_status = Helpers::get_business_settings('delivery_zip_code_area_restriction');

        $countries = $country_restrict_status ? $this->get_delivery_country_array() : COUNTRIES;
        $zip_codes = $zip_restrict_status ? DeliveryZipCode::all() : 0;

        if (auth('customer')->check()) {
            $shippingAddresses = \App\Model\ShippingAddress::where('customer_id', auth('customer')->id())->get();
            return view('web-views.users-profile.account-address', compact('shippingAddresses', 'country_restrict_status', 'zip_restrict_status', 'countries', 'zip_codes'));
        } else {
            return redirect()->route('home');
        }
    }

    public function address_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'city' => 'required',
            'zip' => 'sometimes',
            'country' => 'required',
            'address' => 'required',
        ]);

        $country_restrict_status = Helpers::get_business_settings('delivery_country_restriction');
        $zip_restrict_status = Helpers::get_business_settings('delivery_zip_code_area_restriction');

        $country_exist = self::delivery_country_exist_check($request->country);
        $zipcode_exist = self::delivery_zipcode_exist_check($request->zip);

        if ($country_restrict_status && !$country_exist) {
            Toastr::error(translate('Delivery_unavailable_in_this_country!'));
            return back();
        }

        // if ($zip_restrict_status && !$zipcode_exist) {
        //     Toastr::error(translate('Delivery_unavailable_in_this_zip_code_area!'));
        //     return back();
        // }

        $address = [
            'customer_id' => auth('customer')->check() ? auth('customer')->id() : null,
            'contact_person_name' => $request->name,
            'address_type' => $request->addressAs,
            'address' => $request->address,
            'city' => $request->city,
            'zip' => $request->zip,
            'country' => $request->country,
            'phone' => $request->phone,
            'is_billing' => $request->is_billing,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('shipping_addresses')->insert($address);

        Toastr::success(translate('address_added_successfully!'));

        if(theme_root_path() == 'default'){
            return back();
        }else{
            return redirect()->route('user-profile');
        }
    }

    public function address_edit(Request $request, $id)
    {
        $shippingAddress = ShippingAddress::where('customer_id', auth('customer')->id())->find($id);
        $country_restrict_status = Helpers::get_business_settings('delivery_country_restriction');
        $zip_restrict_status = Helpers::get_business_settings('delivery_zip_code_area_restriction');

        if ($country_restrict_status) {
            $delivery_countries = self::get_delivery_country_array();
        } else {
            $delivery_countries = 0;
        }
        if ($zip_restrict_status) {
            $delivery_zipcodes = DeliveryZipCode::all();
        } else {
            $delivery_zipcodes = 0;
        }
        if (isset($shippingAddress)) {
            return view(VIEW_FILE_NAMES['account_address_edit'], compact('shippingAddress', 'country_restrict_status', 'zip_restrict_status', 'delivery_countries', 'delivery_zipcodes'));
        } else {
            Toastr::warning(translate('access_denied'));
            return back();
        }
    }

    public function address_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'city' => 'required',
            'zip' => 'sometimes',
            'country' => 'required',
            'address' => 'required',
        ]);

        $country_restrict_status = Helpers::get_business_settings('delivery_country_restriction');
        $zip_restrict_status = Helpers::get_business_settings('delivery_zip_code_area_restriction');

        $country_exist = self::delivery_country_exist_check($request->country);
        $zipcode_exist = self::delivery_zipcode_exist_check($request->zip);

        if ($country_restrict_status && !$country_exist) {
            Toastr::error(translate('Delivery_unavailable_in_this_country!'));
            return back();
        }

        // if ($zip_restrict_status && !$zipcode_exist) {
        //     Toastr::error(translate('Delivery_unavailable_in_this_zip_code_area!'));
        //     return back();
        // }

        $updateAddress = [
            'contact_person_name' => $request->name,
            'address_type' => $request->addressAs,
            'address' => $request->address,
            'city' => $request->city,
            'zip' => $request->zip,
            'country' => $request->country,
            'phone' => $request->phone,
            'is_billing' => $request->is_billing,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if (auth('customer')->check()) {
            ShippingAddress::where('id', $request->id)->update($updateAddress);
            Toastr::success(translate('address_updated_successfully!'));
            return redirect()->back();
        } else {
            Toastr::error(translate('Insufficient_permission!'));
            return redirect()->back();
        }
    }

    public function address_delete(Request $request)
    {
        if (auth('customer')->check()) {
            ShippingAddress::destroy($request->id);
            Toastr::success(translate('address_Delete_Successfully'));
            return redirect()->back();
        } else {
            return redirect()->back();
        }
    }

    public function account_payment()
    {
        if (auth('customer')->check()) {
            return view('web-views.users-profile.account-payment');

        } else {
            return redirect()->route('home');
        }

    }

    public function account_oder(Request $request)
    {
        $order_by = $request->order_by ?? 'desc';
        if(theme_root_path() == 'theme_fashion'){
            $show_order = $request->show_order ?? 'ongoing';

            $array = ['pending','confirmed','out_for_delivery','processing'];
            $orders = $this->order->withSum('order_details', 'qty')
                ->where(['customer_id'=> auth('customer')->id(), 'is_guest'=>'0'])
                ->when($show_order == 'ongoing', function($query) use($array){
                    $query->whereIn('order_status',$array);
                })
                ->when($show_order == 'previous', function($query) use($array){
                    $query->whereNotIn('order_status',$array);
                })
                ->when($request['search'], function($query) use($request){
                        $query->where('id', 'like', "%{$request['search']}%");
                })
                ->orderBy('id', $order_by)->paginate(10)->appends(['show_order'=>$show_order, 'search'=>$request->search]);
        }else{
            $orders = Order::where(['customer_id'=> auth('customer')->id(), 'is_guest'=>'0'])
                        ->groupBy('order_group_id')->orderBy('id', $order_by)->paginate(10);
            $orders->getCollection()->map(function ($order) {
                $order->total_amount = self::totalAmountByGroupId($order->order_group_id);
                return $order;
            });
        }
        return view(VIEW_FILE_NAMES['account_orders'], compact('orders', 'order_by'));
    }

    public function totalAmountByGroupId($order_group_id) {
        $order = Order::where('order_group_id', $order_group_id)->sum('order_amount');
        return (float) $order;
    }

    public function account_order_details(Request $request)
    {
        $ordergroupid = $request->id;
        $orders = $this->order->with(['details.product', 'delivery_man_review', 'offline_payments'])
            ->where(['order_group_id' => $request->id, 'customer_id'=>auth('customer')->id(), 'is_guest'=>'0'])
            ->get();

        $orders->map(function($order) {            
            $order->deliverystatus = self::deliveryStatus($order->order_status);
            return $order;
        });

        $refund_day_limit = \App\CPU\Helpers::get_business_settings('refund_day_limit');
        $current_date = \Carbon\Carbon::now();
        
        if(count($orders) > 0){
            $order_by = 'asc';
            return view(VIEW_FILE_NAMES['account_order_details'], compact('orders', 'refund_day_limit', 'current_date', 'order_by', 'ordergroupid'));
        }

        Toastr::warning(translate('invalid_order'));
        return redirect('account-oder');
    }

    private function deliveryStatus ($status) {
        $status = strtolower($status);
        if ($status == 'pending') {
            return 'Awaiting Payment';
        } else if ($status == 'confirmed') {
            return 'Order is in review';
        } else if ($status == 'processing') {
            return 'Order is in Packaging state';
        } else if ($status == 'out_for_delivery') {
            return 'Order is out for delivery';
        } else if ($status == 'delivered') {
            return 'Order Delivered';
        } else if ($status == 'returned') {
            return 'Order Returned';
        } else if ($status == 'failed') {
            return 'Failed to Deliver';
        } else if ($status == 'canceled') {
            return 'Order Canceled';
        }
    }

    public function account_order_details_seller_info(Request $request)
    {
        $order = $this->order->with(['seller.shop'])->find($request->id);
        $product_ids = $this->product->where(['added_by' => $order->seller_is , 'user_id'=>$order->seller_id])->pluck('id');
        $rating = $this->review->whereIn('product_id', $product_ids);
        $avg_rating = $rating->avg('rating') ?? 0 ;
        $rating_percentage = round(($avg_rating * 100) / 5);
        $rating_count = $rating->count();
        $product_count = $this->product->where(['added_by' => $order->seller_is , 'user_id'=>$order->seller_id])->active()->count();

        return view(VIEW_FILE_NAMES['seller_info'], compact('avg_rating', 'product_count', 'rating_count', 'order', 'rating_percentage'));

    }

    public function account_order_details_delivery_man_info(Request $request)
    {
        $order = $this->order->with(['details.product','delivery_man.rating', 'delivery_man'=>function($query){
                return $query->withCount('review');
            }])
            ->find($request->id);
        if(theme_root_path() == 'theme_fashion') {
            foreach($order->details as $details) {
                if($details->product) {
                    if($details->product->product_type == 'physical'){
                        $order['product_type_check'] = $details->product->product_type;
                        break;
                    }else{
                        $order['product_type_check'] = $details->product->product_type;
                    }
                }
            }
        }


        $delivered_count = $this->order->where(['order_status' => 'delivered', 'delivery_man_id' => $order->delivery_man_id, 'delivery_type' => 'self_delivery'])->count();

        return view(VIEW_FILE_NAMES['delivery_man_info'], compact('delivered_count', 'order'));
    }


    public function account_wishlist()
    {
        if (auth('customer')->check()) {
            $wishlists = Wishlist::where('customer_id', auth('customer')->id())->get();
            return view('web-views.products.wishlist', compact('wishlists'));
        } else {
            return redirect()->route('home');
        }
    }

    public function account_tickets()
    {
        if (auth('customer')->check()) {
            $supportTickets = null;
            if(theme_root_path() != 'default') {
                $supportTickets = SupportTicket::where('customer_id', auth('customer')->id())->latest()->paginate(10);
            }
            return view(VIEW_FILE_NAMES['account_tickets'], compact('supportTickets'));
        } else {
            return redirect()->route('home');
        }
    }

    public function ticket_submit(Request $request)
    {
        $ticket = [
            'subject' => $request['ticket_subject'],
            'type' => $request['ticket_type'],
            'customer_id' => auth('customer')->check() ? auth('customer')->id() : null,
            'priority' => $request['ticket_priority'],
            'description' => $request['ticket_description'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('support_tickets')->insert($ticket);
        return back();
    }

    public function single_ticket(Request $request)
    {
        $ticket = SupportTicket::where('id', $request->id)->first();
        return view(VIEW_FILE_NAMES['ticket_view'], compact('ticket'));
    }

    public function comment_submit(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required',
        ], [
            'comment.required' => translate('type_something'),
        ]);

        DB::table('support_tickets')->where(['id' => $id])->update([
            'status' => 'open',
            'updated_at' => now(),
        ]);

        DB::table('support_ticket_convs')->insert([
            'customer_message' => $request->comment,
            'support_ticket_id' => $id,
            'position' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return back();
    }

    public function support_ticket_close($id)
    {
        DB::table('support_tickets')->where(['id' => $id])->update([
            'status' => 'close',
            'updated_at' => now(),
        ]);
        Toastr::success(translate('ticket_closed').'!');
        return redirect('/account-tickets');
    }

    public function account_transaction()
    {
        $customer_id = auth('customer')->id();
        $customer_type = 'customer';
        if (auth('customer')->check()) {
            $transactionHistory = CustomerManager::user_transactions($customer_id, $customer_type);
            return view('web-views.users-profile.account-transaction', compact('transactionHistory'));
        } else {
            return redirect()->route('home');
        }
    }

    public function support_ticket_delete(Request $request)
    {

        if (auth('customer')->check()) {
            $support = SupportTicket::find($request->id);
            $support->delete();
            return redirect()->back();
        } else {
            return redirect()->back();
        }

    }

    public function account_wallet_history($user_id, $user_type = 'customer')
    {
        $customer_id = auth('customer')->id();
        if (auth('customer')->check()) {
            $wallerHistory = CustomerManager::user_wallet_histories($customer_id);
            return view('web-views.users-profile.account-wallet', compact('wallerHistory'));
        } else {
            return redirect()->route('home');
        }

    }

    public function track_order()
    {
        return view(VIEW_FILE_NAMES['tracking-page']);
    }
    public function track_order_wise_result(Request $request)
    {
        if (auth('customer')->check()) {
            $orderDetails = Order::with('order_details')->where('id', $request['order_id'])->whereHas('details', function ($query) {
                $query->where('customer_id', (auth('customer')->id()));
            })->first();
            return view(VIEW_FILE_NAMES['track_order_wise_result'], compact('orderDetails'));
        }
    }

    public function updateOrder(Request $request) {
        $action = $request->input('action');
        $orderIdSelected = $request->order_id;
        $update = false;

        if (empty($orderIdSelected)) {
            Toastr::error('Please select at least one order.');
            return back();
        }
    
        switch($action) {
            case "cancel_order":
                $orderDetails = OrderDetail::whereIn('id', $orderIdSelected)->whereIn('delivery_status', ['pending', 'confirmed'])->get();             
                $orderReference = null;
                
                if ($orderDetails->count() < 1) {
                    Toastr::error(translate('order_already_treated'));
                    return back();
                }

                try {
                    $transactionSuccess = true; // Flag to track transaction success
    
                    DB::transaction(function () use ($orderDetails, &$orderReference, &$transactionSuccess) {
                        foreach ($orderDetails as $detailKey => $detail) {
                            $orderInfo = json_decode($detail->order_info, true);
                            $orderReference = $orderInfo['reference'];
                            $productDetail = json_decode($detail->product_details, true);
                            $productId = $productDetail['id'];
                            $product = Product::find($productId);
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
    
                            $getOrder = Order::where('order_group_id', $orderReference)->first();
                            $customer_id = $getOrder->customer_id;
                            
                            // Let's deduct delivery charge and commission back from the admin...
                            $adminWallet = AdminWallet::where('admin_id', 1)->first();
                            $adminWallet->delivery_charge_escrow -= $deliveryFee;
                            $adminWallet->commission_escrow -= $adminCommission;

                            if ($isSellerAdmin) {
                                $adminWallet->pending_amount -= $sellerFee;
                            } else {
                                // Remove the money from seller's wallet...
                                $sellerWallet = SellerWallet::where(['seller_id' => $detail->seller_id])->first();
                                $sellerWallet->escrow_balance -= $sellerFee;
                                $sellerWallet->save();
                            }
                            $adminWallet->save();

                            $amountToRefund = (float) ($deliveryFee + $adminCommission + $sellerFee);

                            // Let's get the Customer details...
                            $user = User::where(['id' => $customer_id])->first();
                            $userWalletBalance = (float) $user->wallet_balance;
                            $userNewBalance = $userWalletBalance + $amountToRefund;

                            $user->wallet_balance = (float) $userNewBalance;
                            $user->save();

                            // Create a wallet refund transaction history...
                            WalletTransaction::create([
                                'user_id' => $customer_id,
                                'transaction_id' => 'refund-'.$getOrder->id,
                                'credit' => $amountToRefund,
                                'balance' => $userNewBalance,
                                'transaction_type' => 'order_refund',
                                'reference' => 'order refund'
                            ]);

                            // Let's return the product since seller still have it
                            if ($product) {
                                // Product has some variation, e.g gold, cyan e.t.c
                                if (!empty($detailVariant)) {
                                    $productVariation = json_decode($product->variation, true);
                                    foreach ($productVariation as $variationIndex => $variation) {
                                        if ($variation['type'] == $detailVariant) {
                                            $productVariation[$variationIndex]['qty'] += $quantityBuy;
                                            $product->current_stock += $quantityBuy;
                                            break;
                                        }
                                    }
                                    $product->variation = json_encode($productVariation);
                                } else {
                                    $product->current_stock += $quantityBuy;
                                }
                                $product->save();
                            }
                        }
                    });                    

                    if ($transactionSuccess) {
                        $update = OrderDetail::whereIn('id', $orderIdSelected)->update(['delivery_status' => 'canceled']);
                        if ($update) {
                            // Get total number of order details and canceled order details for this order reference
                            $queryDetail = OrderDetail::whereJsonContains('order_info->reference', $orderReference);
                            $totalOrderDetails = $queryDetail->count();
                            $totalCanceledDetails = $queryDetail->where('delivery_status', 'canceled')->count();
                            // If all order details are canceled, update the order status to canceled
                            if ($totalOrderDetails === $totalCanceledDetails) {
                                Order::where('order_group_id', $orderReference)->update(['order_status' => 'canceled']);
                            }
                        }
                    }
                } catch (Exception $e) {
                    Log::error($e->getMessage());
                    $update = false;
                }
    
            break;
    
            case "lodge_complaint":
                $update = OrderDetail::whereIn('id', $orderIdSelected)->update(['delivery_status' => $action]);
            break;

            case "confirm_delivery":
                
                $orderDetails = OrderDetail::whereIn('id', $orderIdSelected)->whereIn('delivery_status', ['pending', 'confirmed', 'out_for_delivery'])->get();             
                $orderReference = null;
                                
                if ($orderDetails->count() < 1) {
                    Toastr::error(translate('order_already_treated'));
                    return back();
                }
                $update = OrderManager::distribute_delivered_order_funds($orderIdSelected);
            break;
        }
    
        if (!$update) {
            Toastr::error(translate('error_updating_order_status'));
            return back();
        }
        Toastr::success(translate('order_status_updated_successfully'));
        return back();
    }

    public function track_order_result(Request $request)
    {
        $orderId = $request['order_id'];
        // $orderId = 100001;
        $orderDetail = is_numeric($orderId) ? OrderDetail::where('order_id', $orderId)->get() : 
            Order::with(['details'])->where(['order_group_id' => $orderId])->first();

        if (!$orderDetail) {
            Session::flash('error', 'Invalid order Id provided, please provide a valid order id');
            return redirect()->back()->withInput();
        }

        if (isset($orderDetail->details)) {
            $orderDetail = $orderDetail->details;
            $orderId = $orderDetail[0]->order_id;
        }

        $orderPhone = Order::with(['shippingAddress:id,contact_person_name,address,phone'])->where('id', $orderId)->first();
        $shippingData = $orderPhone->shippingAddress;
        $deliveryInfo = [];
        $deliveryInfo['name'] = $shippingData->contact_person_name;
        $deliveryInfo['phone'] = $shippingData->phone;
        $deliveryInfo['address'] = $shippingData->address;
        $orderId = $request['order_id'];
        $orderInfo = $orderPhone;
        // return $orderDetail;
        return view(VIEW_FILE_NAMES['track_order'], compact('orderDetail', 'orderInfo', 'deliveryInfo', 'orderId'));
    }

    public function track_last_order()
    {
        $orderDetails = OrderManager::track_order(Order::where('customer_id', auth('customer')->id())->latest()->first()->id);

        if ($orderDetails != null) {
            return view('web-views.order.tracking', compact('orderDetails'));
        } else {
            return redirect()->route('track-order.index')->with('Error', \App\CPU\translate('invalid_Order_Id_or_phone_Number'));
        }

    }

    public function order_cancel($id)
    {
        $order = Order::where(['id' => $id])->first();
        if ($order['payment_method'] == 'cash_on_delivery' && $order['order_status'] == 'pending') {
            OrderManager::stock_update_on_order_status_change($order, 'canceled');
            Order::where(['id' => $id])->update([
                'order_status' => 'canceled'
            ]);
            Toastr::success(translate('successfully_canceled'));
            return back();
        }
        Toastr::error(translate('status_not_changable_now'));
        return back();
    }

    public function refund_request(Request $request, $id)
    {
        $order_details = OrderDetail::find($id);
        $user = auth('customer')->user();

        $wallet_status = Helpers::get_business_settings('wallet_status');
        $loyalty_point_status = Helpers::get_business_settings('loyalty_point_status');
        if ($loyalty_point_status == 1) {
            $loyalty_point = CustomerManager::count_loyalty_point_for_amount($id);

            if ($user->loyalty_point < $loyalty_point) {
                Toastr::warning(translate('you_have_not_sufficient_loyalty_point_to_refund_this_order').'!!');
                return back();
            }
        }

        return view('web-views.users-profile.refund-request', compact('order_details'));
    }

    public function store_refund(Request $request)
    {
        $request->validate([
            'order_details_id' => 'required',
            'amount' => 'required',
            'refund_reason' => 'required'

        ]);
        $order_details = OrderDetail::find($request->order_details_id);
        $user = auth('customer')->user();


        $loyalty_point_status = Helpers::get_business_settings('loyalty_point_status');
        if ($loyalty_point_status == 1) {
            $loyalty_point = CustomerManager::count_loyalty_point_for_amount($request->order_details_id);

            if ($user->loyalty_point < $loyalty_point) {
                Toastr::warning(translate('you_have_not_sufficient_loyalty_point_to_refund_this_order').'!!');
                return back();
            }
        }
        $refund_request = new RefundRequest;
        $refund_request->order_details_id = $request->order_details_id;
        $refund_request->customer_id = auth('customer')->id();
        $refund_request->status = 'pending';
        $refund_request->amount = $request->amount;
        $refund_request->product_id = $order_details->product_id;
        $refund_request->order_id = $order_details->order_id;
        $refund_request->refund_reason = $request->refund_reason;

        if ($request->file('images')) {
            foreach ($request->file('images') as $img) {
                $product_images[] = ImageManager::upload('refund/', 'png', $img);
            }
            $refund_request->images = json_encode($product_images);
        }
        $refund_request->save();

        $order_details->refund_request = 1;
        $order_details->save();

        Toastr::success(translate('refund_requested_successful!!'));
        return redirect()->route('account-order-details', ['id' => $order_details->order_id]);
    }

    public function generate_invoice($id)
    {
        $order = Order::with('seller')->with('shipping')->where('id', $id)->first();
        $data["email"] = $order->customer["email"];
        $data["order"] = $order;

        $mpdf_view = \View::make(VIEW_FILE_NAMES['order_invoice'], compact('order'));
        Helpers::gen_mpdf($mpdf_view, 'order_invoice_', $order->id);
    }

    public function refund_details($id)
    {
        $order_details = OrderDetail::find($id);
        $refund = RefundRequest::where('customer_id', auth('customer')->id())
            ->where('order_details_id', $order_details->id)->first();
        $product = $this->product->find($order_details->product_id);
        $order = $this->order->find($order_details->order_id);

        if($product) {
            return view(VIEW_FILE_NAMES['refund_details'], compact('order_details', 'refund', 'product', 'order'));
        }

        Toastr::error(translate('product_not_found'));
        return redirect()->back();
    }

    public function submit_review(Request $request, $id)
    {
        $order_details = OrderDetail::where(['id' => $id])->whereHas('order', function ($q) {
            $q->where(['customer_id' => auth('customer')->id(), 'payment_status' => 'paid']);
        })->first();

        if (!$order_details) {
            Toastr::error(translate('invalid_order!'));
            return redirect('/');
        }

        return view('web-views.users-profile.submit-review', compact('order_details'));

    }

    public function refer_earn(Request $request)
    {
        $ref_earning_status = Helpers::get_business_settings('ref_earning_status') ?? 0;
        if(!$ref_earning_status){
            Toastr::error(translate('you_have_no_permission'));
            return redirect('/');
        }
        $customer_detail = User::where('id', auth('customer')->id())->first();

        return view(VIEW_FILE_NAMES['refer_earn'], compact('customer_detail'));
    }

    public function user_coupons(Request $request)
    {
        $seller_ids = Seller::approved()->pluck('id')->toArray();
        $seller_ids = array_merge($seller_ids, [NULL, '0']);

        $coupons = Coupon::with('seller')
                    ->where(['status' => 1])
                    ->whereIn('customer_id',[auth('customer')->id(), '0'])
                    ->whereIn('customer_id',[auth('customer')->id(), '0'])
                    ->whereDate('start_date', '<=', date('Y-m-d'))
                    ->whereDate('expire_date', '>=', date('Y-m-d'))
                    ->paginate(8);

        return view(VIEW_FILE_NAMES['user_coupons'], compact('coupons'));
    }
}
