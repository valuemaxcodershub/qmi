<?php

namespace App\Http\Controllers\Customer\Auth;

use App\User;
use Carbon\Carbon;
use App\CPU\Helpers;
use App\Mail\LoginOTP;
use App\Model\Wishlist;
use App\CPU\CartManager;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use App\Model\ProductCompare;
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
    public $company_name;

    public function __construct()
    {
        $this->middleware('guest:customer', ['except' => ['logout']]);
    }

    public function captcha(Request $request, $tmp)
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
        return view(VIEW_FILE_NAMES['login_view']);
        
        if(theme_root_path() == 'default'){
            return view('customer-views.auth.login');
        }else{
            return redirect()->route('home');
        }
    }

    public function register()
    {
        return view(VIEW_FILE_NAMES['register_view']);
    }

    public function submit(Request $request)
    {

        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $userEmail = $request->email;
        $user = User::where(['email' => $userEmail])->first();
        $remember = ($request['remember']) ? true : false;
        
        if ($user) {
            if ($user->is_active) {
                // Password matches
                if (Hash::check($request->password, $user->password)) {
                    $otpToken =  app(OtpTokenController::class)->generateOtpToken($user->id, 'user', 'login');
                    if ($otpToken === false) {
                        Toastr::success('Error generating OTP Token');
                        return redirect()->back();
                    }
                    $clientName = $user->f_name . " ". $user->l_name;
                    Mail::to($user->email)->send(new LoginOTP($clientName, 'buyer', $otpToken));
                    
                    Toastr::success('OTP code has been sent to your email address.');
                    return redirect()->route('customer.auth.verify-otp')->with(['email' => $userEmail, 'name' => $clientName]);
                } else {
                    // Password does not match
                    Toastr::error('Invalid email or password.');
                    return redirect()->back()->withInput();
                }
            } else {
                $ajax_message = [
                    'status'=>'error',
                    'message'=> translate('account_is_pending_activation_please_check_your_email'),
                    'redirect_url'=>''
                ];
                Toastr::error(translate('account_is_pending_activation_please_check_your_email'));
                
                if($request->ajax()) {
                    return response()->json($ajax_message);
                }else{
                    return back()->withInput();
                }
            }
        } else {
            $ajax_message = [
                'status'=>'error',
                'message'=> translate('credentials_do_not_match'),
                'redirect_url'=>''
            ];
            Toastr::error(translate('credentials_do_not_match'));
            
            if($request->ajax()) {
                return response()->json($ajax_message);
            }else{
                return back()->withInput();
            }
        }
    }

    public function otpVerifyView() {
        $name = session('name');
        $email = session('email');

        if (!$email OR !$name) {
            return redirect()->route('customer.auth.login');
        }
        return view(VIEW_FILE_NAMES['verifyotp_view'], compact('name', 'email'));
    }

    public function submitOTP(Request $request) {
        $otpToken = $request->otpcode;

        if (empty($otpToken)) {
            Toastr::error('Please enter a valid otp code');
            return redirect()->route('customer.auth.login');
        }

        $otpInit = new OtpTokenController();
        $verifyOTP = $otpInit->verifyOtp($otpToken, 'user');

        if (!$verifyOTP['status']) {
            Toastr::error($verifyOTP['message']);
            return redirect()->route('customer.auth.login');
        }

        $userId = $verifyOTP['data']['user_id'];
        $otpInit->updateOtp($otpToken, 'used');
        auth()->guard('customer')->loginUsingId($userId);

        return redirect()->route('user-profile');
    }   

    public function logout(Request $request)
    {
        auth()->guard('customer')->logout();
        session()->forget('wish_list');
        Toastr::info(translate('come_back_soon').'!');
        return redirect()->route('home');
    }
}
