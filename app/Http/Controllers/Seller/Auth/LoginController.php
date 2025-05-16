<?php

namespace App\Http\Controllers\Seller\Auth;

use App\Model\Shop;
use App\CPU\Helpers;
use App\Model\Banner;
use App\Model\Seller;
use App\Mail\LoginOTP;
use App\Model\Product;
use App\Model\Category;
use App\Model\Currency;
use App\Model\FlashDeal;
use App\Model\SocialMedia;
use App\Model\SellerWallet;
use Illuminate\Http\Request;
use App\Model\BusinessSetting;
use Gregwar\Captcha\PhraseBuilder;
use Illuminate\Support\Facades\DB;
use Gregwar\Captcha\CaptchaBuilder;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\OtpTokenController;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:seller', ['except' => ['logout']]);
    }

    public function captcha(Request $request,$tmp)
    {

        $phrase = new PhraseBuilder;
        $code = $phrase->build(4);
        $builder = new CaptchaBuilder($code, $phrase);
        $builder->setBackgroundColor(220, 210, 230);
        $builder->setMaxAngle(25);
        $builder->setMaxBehindLines(0);
        $builder->setMaxFrontLines(0);
        $builder->build($width = 100, $height = 40, $font = null);
        $phrase = $builder->getPhrase();

        if(Session::has($request->captcha_session_id)) {
            Session::forget($request->captcha_session_id);
        }
        Session::put($request->captcha_session_id, $phrase);
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type:image/jpeg");
        $builder->output();
    }

    public function login()
    {
        return view('seller-views.auth.login');
    }

    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required|min:5'
        ]);
        
        if ($validator->fails() AND $request->ajax()) {
            return response()->json([
                'errors' => $validator->errors()->all()
            ]);
        }
        $seller = Seller::where(['email' => $request['email']])->first();

        if (isset($seller)) {
            if ($seller['status'] == 'pending') {
                return redirect()->back()->withInput($request->only('email', 'remember'))->withErrors(['Your account is not approved yet.']);
            } else if ($seller['status'] == 'suspended') {
                return redirect()->back()->withInput($request->only('email', 'remember'))->withErrors(['Your account is suspended.']);
            } else {
                if (SellerWallet::where('seller_id', $seller->id)->first() == false) {
                    DB::table('seller_wallets')->insert([
                        'seller_id' => auth('seller')->id(),
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

                if (Hash::check($request->password, $seller->password)) {
                    $otpToken =  app(OtpTokenController::class)->generateOtpToken($seller->id, 'seller', 'login');
                    if ($otpToken === false) {
                        Toastr::success('Error generating OTP Token');
                        return redirect()->back();
                    }
                    $clientName = $seller->f_name . " ". $seller->l_name;
                    $sellerEmail = $seller->email; 
                    
                    $web_config['currency_model'] = Helpers::get_business_settings('currency_model');

                    Mail::to($sellerEmail)->send(new LoginOTP($clientName, 'seller', $otpToken));
                
                    Toastr::success('OTP code has been sent to your email address.');
                    return redirect()->route('seller.auth.verify-otp')->with(['email' => $sellerEmail, 'name' => $clientName]);
                    
                } else {
                    return redirect()->back()->withInput($request->only('email', 'remember'))->withErrors(['Credentials does not match.']);
                }
            }
        } else{
            return redirect()->back()->withInput($request->only('email', 'remember'))->withErrors(['Credentials does not match.']);
        }
    }

    public function otpVerifyView() {
        $name = session('name');
        $email = session('email');
        
        if (!$email OR !$name) {
            return redirect()->route('seller.auth.login');
        }

        return view('seller-views.auth.loginotp', compact('name', 'email'));
    }

    public function submitOtpLogin(Request $request) {
        $otpToken = $request->otptoken;

        if (empty($otpToken)) {
            Toastr::error('Please enter a valid otp code');
            return redirect()->route('seller.auth.login');
        }

        $otpInit = new OtpTokenController();
        $verifyOTP = $otpInit->verifyOtp($otpToken, 'seller');

        if (!$verifyOTP['status']) {
            Toastr::error($verifyOTP['message']);
            return redirect()->route('seller.auth.login');
        }

        $sellerId = $verifyOTP['data']['user_id'];
        $otpInit->deleteOtp($otpToken);
        auth()->guard('seller')->loginUsingId($sellerId);
        return redirect()->route('seller.dashboard.index');
    }

    public function logout(Request $request)
    {
        auth()->guard('seller')->logout();

        $request->session()->invalidate();

        return redirect()->route('seller.auth.login');
    }
}
