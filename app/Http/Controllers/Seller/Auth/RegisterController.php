<?php

namespace App\Http\Controllers\Seller\Auth;

use Carbon\Carbon;
use App\Model\Shop;
use App\CPU\Helpers;
use App\Model\Seller;
use App\CPU\ImageManager;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\SellerRegVerify;
use App\Model\BusinessSetting;
use function App\CPU\translate;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;
use App\Model\PhoneOrEmailVerification;
use Illuminate\Support\Facades\Session;

class RegisterController extends Controller
{
    public function create()
    {
        $business_mode=Helpers::get_business_settings('business_mode');
        $seller_registration=Helpers::get_business_settings('seller_registration');
        if((isset($business_mode) && $business_mode=='single') || (isset($seller_registration) && $seller_registration==0))
        {
            Toastr::warning(translate('access_denied!!'));
            return redirect('/');
        }
        return view(VIEW_FILE_NAMES['seller_registration']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'image'         => 'required|mimes: jpg,jpeg,png,gif',
            'logo'          => 'required|mimes: jpg,jpeg,png,gif',
            'banner'        => 'required|mimes: jpg,jpeg,png,gif',
            'bottom_banner' => 'mimes: jpg,jpeg,png,gif',
            'email'         => 'required|unique:sellers',
            'shop_address'  => 'required',
            'f_name'        => 'required',
            'l_name'        => 'required',
            'shop_name'     => 'required',
            'phone'         => 'required',
            'password'      => 'required|min:5'
        ],
        [

            'image.required'  => translate('image_is_required').'!',
            'logo.required'  => translate('logo_name_is_required').'!',
            'banner.required'  => translate('banner_name_is_required').'!',
            'bottom_banner.required'  => translate('bottom_banner_name_is_required').'!',
            'shop_address.required'  => translate('shop_address_is_required').'!',
        ]
        );

        DB::transaction(function ($r) use ($request) {
            $seller = new Seller();
            $seller->f_name = $request->f_name;
            $seller->l_name = $request->l_name;
            $seller->phone = $request->phone;
            $seller->email = $request->email;
            $seller->image = ImageManager::upload('seller/', 'png', $request->file('image'));
            $seller->password = bcrypt($request->password);
            // $seller->status =  $request->status == 'approved'?'approved': "pending";
            $seller->status = "pending";
            $seller->seller_type =  BusinessSetting::where('type', 'default_seller_type')->first()->value;
            $seller->save();

            $shop = new Shop();
            $shop->seller_id = $seller->id;
            $shop->name = $request->shop_name;
            $shop->address = $request->shop_address;
            $shop->contact = $request->phone;
            $shop->image = ImageManager::upload('shop/', 'png', $request->file('logo'));
            $shop->banner = ImageManager::upload('shop/banner/', 'png', $request->file('banner'));
            $shop->bottom_banner = ImageManager::upload('shop/banner/', 'png', $request->file('bottom_banner'));
            $shop->save();

            DB::table('seller_wallets')->insert([
                'seller_id' => $seller['id'],
                'withdrawn' => 0,
                'commission_given' => 0,
                'total_earning' => 0,
                'pending_withdraw' => 0,
                'delivery_charge_earned' => 0,
                'collected_cash' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $phone_email_verify = new PhoneOrEmailVerification();
            $token = Str::random(20);

            $phone_email_verify->phone_or_email = $request->email;
            $phone_email_verify->use_case = 'register';
            $phone_email_verify->token = $token;
            $phone_email_verify->user_type = 'seller';
            $phone_email_verify->save();

            $sellerName = $request->f_name . ' '.$request->l_name;
            Mail::to($request->email)->send(new SellerRegVerify($sellerName, $token));

        });

        // if($request->status == 'approved'){
        //     Toastr::success(translate('shop_apply_successfully'));
        //     return back();
        // }else{
        //     Toastr::success(translate('shop_apply_successfully'));
        //     return redirect()->route('seller.auth.login');
        // }

        Toastr::success(translate('seller_reg_successful_verify'));
        return redirect()->route('seller.auth.login');

    }

    public function verifySeller($token) {
        $tokenVerify = PhoneOrEmailVerification::where(['token' => $token, 'user_type' => 'seller'])->first();

        if ($tokenVerify) {
            if (!is_null($tokenVerify->expires_at)) {
                Toastr::success('Seller account already verified');
                return redirect()->route('seller.auth.login');
            }
            Seller::where(['email' => $tokenVerify->phone_or_email])->update(['status' => 'approved']);
            PhoneOrEmailVerification::where(['token' => $token, 'user_type' => 'seller'])->update(['expires_at' => Carbon::now()]);
            Toastr::success('Your account has been verified successfully');
            return redirect()->route('seller.auth.login');
        }
        Toastr::success('Invalid verification link used');
        return redirect()->route('seller.auth.login');        
    }
}
