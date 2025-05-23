<?php

namespace App\Http\Controllers\Seller\Auth;

use App\CPU\Helpers;
use App\Model\Seller;
use App\CPU\SMS_module;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use function App\CPU\translate;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;
use Modules\Gateways\Traits\SmsGateway;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:seller', ['except' => ['logout']]);
    }

    public function forgot_password()
    {
        return view('seller-views.auth.forgot-password');
    }

    public function reset_password_request(Request $request)
    {
        $request->validate([
            'identity' => 'required',
        ]);
 
        session()->put('forgot_password_identity', $request['identity']);
        $verification_by = Helpers::get_business_settings('forgot_password_verification');

        if($verification_by == 'email')
        {
            $seller = Seller::Where(['email' => $request['identity']])->first();
            if (isset($seller)) {
                $token = Str::random(120);
                DB::table('password_resets')->insert([
                    'identity' => $seller['email'],
                    'token' => $token,
                    'user_type'=>'seller',
                    'created_at' => now(),
                ]);
                $reset_url = url('/') . '/seller/auth/reset-password?token=' . $token;
                $customerName = $seller->f_name . ' '. $seller->l_name;
                Mail::to($seller['email'])->send(new \App\Mail\PasswordResetMail($reset_url, $customerName));

                Toastr::success(translate('Check_your_email'). translate('Password_reset_url_sent'));
                return back();
            }
        }elseif ($verification_by == 'phone') {
            $seller = Seller::Where('phone', 'like', "%{$request['identity']}%")->first();
            if (isset($seller)) {
                $token = rand(1000, 9999);
                DB::table('password_resets')->insert([
                    'identity' => $seller['phone'],
                    'token' => $token,
                    'user_type'=>'seller',
                    'created_at' => now(),
                ]);

                $published_status = 0;
                $payment_published_status = config('get_payment_publish_status');
                if (isset($payment_published_status[0]['is_published'])) {
                    $published_status = $payment_published_status[0]['is_published'];
                }

                $response = '';
                if($published_status == 1){
                    $response = SMS_module::send($seller->phone, $token);
                }else{
                    $response = SmsGateway::send($seller->phone, $token);
                }

                if ($response == "not_found") {
                    Toastr::error(translate('SMS_configuration_missing'));
                    return back();
                }

                Toastr::success(translate('Check_your_phone').' '.translate('Password_reset_otp_sent'));
                return redirect()->route('seller.auth.otp-verification');
            }
        }

        Toastr::error(translate('No_such_user_found').'!');
        return back();
    }

    public function reset_password_index(Request $request)
    {
        $data = DB::table('password_resets')->where('user_type','seller')->where(['token' => $request['token']])->first();
        if (isset($data)) {
            $token = $request['token'];
            return view('seller-views.auth.reset-password', compact('token')); 
        }
        Toastr::error(translate('Invalid_URL'));
        return redirect('/seller/auth/login');
    }

    public function otp_verification()
    {
        return view('seller-views.auth.verify-otp');
    }

    public function otp_verification_submit(Request $request)
    {
        $id = session('forgot_password_identity');
        $data = DB::table('password_resets')->where('user_type','seller')->where(['token' => $request['otp']])
            ->where('identity', 'like', "%{$id}%")
            ->first();
        if (isset($data)) {
            $token = $request['otp'];
            return redirect()->route('seller.auth.reset-password', ['token' => $token]);
        }

        Toastr::error(translate('invalid_otp'));
        return back();
    }

    public function reset_password_submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|same:confirm_password|min:5',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $data = DB::table('password_resets')->where(['token' => $request['reset_token'], 'user_type' => 'seller'])->first();

        if (isset($data)) {
            DB::table('sellers')->where(['email' => $data->identity])
                                ->orWhere(['phone' => $data->identity])->update([
                'password' => bcrypt($request['password'])
            ]);
            Toastr::success(translate('Password_reset_successfully'));
            DB::table('password_resets')->where('user_type','seller')->where(['token' => $request['reset_token']])->delete();
            return redirect('/seller/auth/login');
        }
        Toastr::error(translate('Invalid_URL'));
        return redirect('/seller/auth/login');
    }
}
