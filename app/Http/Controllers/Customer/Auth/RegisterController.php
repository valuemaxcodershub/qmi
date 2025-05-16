<?php

namespace App\Http\Controllers\Customer\Auth;

use App\User;
use Exception;
use Carbon\Carbon;
use App\CPU\Helpers;
use App\CPU\SMS_module;
use App\Model\Wishlist;
use App\CPU\CartManager;
use Carbon\CarbonInterval;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Model\BusinessSetting;
use App\Mail\EmailVerification;
use function App\CPU\translate;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Model\PhoneOrEmailVerification;
use Illuminate\Support\Facades\Session;
use Modules\Gateways\Traits\SmsGateway;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    private $user;
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->middleware('guest:customer', ['except' => ['logout']]);
    }

    public function verifyAccount($code) {
        $isVerify = PhoneOrEmailVerification::where(['token' => $code, 'use_case' => 'verification'])->first();
        if ($isVerify) {
            $email = $isVerify->phone_or_email;
            $user = User::where('email', $email)->first();
            $user->is_active = 1;
            $user->email_verified_at = Carbon::now();
            $user->is_email_verified = 1;
            $user->save();
            $isVerify->delete();
            Toastr::success('Verification done successfully. You can now login to your account');
            return redirect()->route('customer.auth.login');
        }
        Toastr::error('Invalid verification code');
        return redirect(route('customer.auth.login'));
    }

    public function register()
    {
        session()->put('keep_return_url', url()->previous());
        return view('customer-views.auth.register');
    }

    public function submit(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'f_name' => 'required',
                'l_name' => 'required',
                'email' => 'required|email|unique:users',
                'phone' => 'unique:users',
                'password' => 'required|min:5|same:con_password'
            ], [
                'f_name.required' => translate('first_name_is_required'),
                'l_name.required' => translate('last_name_is_required'),
                'email.unique' => translate('email_already_has_been_taken'),
                'phone.unique' => translate('phone_number_already_has_been_taken'),
            ]);
    
            if($request->ajax()) {
                if ($validator->fails()) {
                    return response()->json([
                        'errors' => $validator->errors()->all()
                    ]);
                }
            }else {
                $validator->validate();
            }
    
            if ($request->referral_code){
                $refer_user = User::where(['referral_code' => $request->referral_code])->first();
            }
    
            $buyerEmail = $request['email'];
            DB::beginTransaction();
            $user = User::create([
                'f_name' => $request['f_name'],
                'l_name' => $request['l_name'],
                'email' => $buyerEmail,
                'phone' => $request['phone'],
                'is_active' => 0,
                'wallet_balance' => 0,
                'password' => Hash::make($request['password']),
                'referral_code' => Helpers::generate_referer_code(),
                'referred_by' => $refer_user->id ?? null,
            ]);
            // return $user;
            if ($user) {
                self::varificaton_check($user->id);
                DB::commit();
                Toastr::success(translate('registration_successful_please_check_your_email_to_complete_registration'));
                return redirect(route('customer.auth.login'));
            } else {
                DB::rollBack();
                Toastr::error(translate('registration_failed'));
                return back();
            }
    
            // $phone_verification = Helpers::get_business_settings('phone_verification');
            // $email_verification = Helpers::get_business_settings('email_verification');
    
            // if($request->ajax()) {
            //     if ($phone_verification && !$user->is_phone_verified) {
            //         self::varificaton_check($user->id);
            //         return response()->json([
            //             'redirect_url'=>route('customer.auth.check', [$user->id]),
            //         ]);
            //     }
            //     if ($email_verification && !$user->is_email_verified) {
            //         self::varificaton_check($user->id);
            //         return response()->json([
            //             'redirect_url'=>route('customer.auth.check', [$user->id]),
            //         ]);
            //     }
            //     self::varificaton_check($user->id);
            //     return response()->json([
            //         'redirect_url'=>'',
            //     ]);
    
            // }else {
            //     if ($phone_verification && !$user->is_phone_verified) {
            //         self::varificaton_check($user->id);
            //         return redirect(route('customer.auth.check', [$user->id]));
            //     }
            //     if ($email_verification && !$user->is_email_verified) {
            //         self::varificaton_check($user->id);
            //         return redirect(route('customer.auth.check', [$user->id]));
            //     }
            //     self::varificaton_check($user->id);
            //     Toastr::success(translate('registration_success_login_now'));
            //     return redirect(route('customer.auth.login'));
            // }
        } catch (Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect(route('customer.auth.login'));
        }
    }

    public static function varificaton_check($id) : bool
    {
        $user = User::find($id);

        // Time Difference in Minutes

        // $token = rand(1000, 9999);
        $token = Str::random(32);
        $createToken = PhoneOrEmailVerification::create([
            'phone_or_email' => $user->email,
            'token' => $token,
            'user_type' => 'customer',
            'use_case' => 'verification',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($createToken) {
            Mail::to($user->email)->send(new \App\Mail\EmailVerification($user, $token));
            return true;
        }
        return false;

        // $phone_verification = Helpers::get_business_settings('phone_verification');
        // $email_verification = Helpers::get_business_settings('email_verification');
        // if ($phone_verification && !$user->is_phone_verified) {

        //     $published_status = 0;
        //     $payment_published_status = config('get_payment_publish_status');
        //     if (isset($payment_published_status[0]['is_published'])) {
        //         $published_status = $payment_published_status[0]['is_published'];
        //     }

        //     $response = '';
        //     if($published_status == 1){
        //         SMS_module::send($user->phone, $token);
        //     }else{
        //         SmsGateway::send($user->phone, $token);
        //     }

        //     $response = translate('please_check_your_SMS_for_OTP');
        //     Toastr::success($response);
        // }

        // if ($email_verification && !$user->is_email_verified) {
        //     $emailServices_smtp = Helpers::get_business_settings('mail_config');
        //     if ($emailServices_smtp['status'] == 0) {
        //         $emailServices_smtp = Helpers::get_business_settings('mail_config_sendgrid');
        //     }
        //     if ($emailServices_smtp['status'] == 1) {
        //         try{
        //             Mail::to($user->email)->send(new \App\Mail\EmailVerification($token));
        //             $response = translate('check_your_email');
        //         } catch (\Exception $exception) {
        //             Toastr::error(translate('email_is_not_configured').'. '.translate('contact_with_the_administrator'));
        //             return back();
        //         }
        //     }else{
        //         $response= translate('email_failed');
        //     }
        //     Toastr::success($response);
        // }
    }

    public static function check($id)
    {
        $phone_verification = Helpers::get_business_settings('phone_verification');
        $email_verification = Helpers::get_business_settings('email_verification');

        $user = User::find($id);
        if($phone_verification){
            $user_verify = $user->is_phone_verified == 1 ? 1 : 0;
        }elseif($email_verification){
            $user_verify = $user->is_email_verified == 1 ? 1 : 0;
        }

        $token = PhoneOrEmailVerification::where('phone_or_email','=',$user->email)->first();
        if($token){
            $otp_resend_time = Helpers::get_business_settings('otp_resend_time') > 0 ? Helpers::get_business_settings('otp_resend_time') : 0;
            $token_time = Carbon::parse($token->created_at);
            $convert_time = $token_time->addSeconds($otp_resend_time);
            $get_time = $convert_time > Carbon::now() ? Carbon::now()->diffInSeconds($convert_time) : 0;
        }else{
            $get_time = 0;
        }

        return view(VIEW_FILE_NAMES['customer_auth_verify'], compact('user','user_verify','get_time'));
    }

    // Customer Default Verify
    public static function verify(Request $request)
    {
        Validator::make($request->all(), [
            'token' => 'required',
        ]);

        $email_status = Helpers::get_business_settings('email_verification');
        $phone_status = Helpers::get_business_settings('phone_verification');

        $user = User::find($request->id);
        $verify = PhoneOrEmailVerification::where(['phone_or_email' => $user->email, 'token' => $request['token']])->first();

        $max_otp_hit = Helpers::get_business_settings('maximum_otp_hit') ?? 5;
        $temp_block_time = Helpers::get_business_settings('temporary_block_time') ?? 5; //minute

        if (isset($verify)) {
            if(isset($verify->temp_block_time ) && Carbon::parse($verify->temp_block_time)->diffInSeconds() <= $temp_block_time){
                $time = $temp_block_time - Carbon::parse($verify->temp_block_time)->diffInSeconds();

                Toastr::error(translate('please_try_again_after_').CarbonInterval::seconds($time)->cascade()->forHumans());
                return redirect()->back();
            }

            ($email_status == 1 || ($phone_status == '0' && $email_status == '0')) ? ($user->is_email_verified = 1) : ($user->is_phone_verified = 1);
            $user->save();
            $verify->delete();

            Toastr::success(translate('verification_done_successfully'));
            return redirect(route('customer.auth.login'));

        }else{
            $verification = PhoneOrEmailVerification::where(['phone_or_email' => $user->email])->first();

            if($verification){
                if(isset($verification->temp_block_time) && Carbon::parse($verification->temp_block_time)->diffInSeconds() <= $temp_block_time){
                    $time= $temp_block_time - Carbon::parse($verification->temp_block_time)->diffInSeconds();

                    Toastr::error(translate('please_try_again_after_').CarbonInterval::seconds($time)->cascade()->forHumans());

                }elseif($verification->is_temp_blocked == 1 && isset($verification->created_at) && Carbon::parse($verification->created_at)->diffInSeconds() >= $temp_block_time){
                    $verification->otp_hit_count = 1;
                    $verification->is_temp_blocked = 0;
                    $verification->temp_block_time = null;
                    $verification->updated_at = now();
                    $verification->save();

                    Toastr::error(translate('Verification code/ OTP mismatched'));

                }elseif($verification->otp_hit_count >= $max_otp_hit && $verification->is_temp_blocked == 0){
                    $verification->is_temp_blocked = 1;
                    $verification->temp_block_time = now();
                    $verification->updated_at = now();
                    $verification->save();

                    $time= $temp_block_time - Carbon::parse($verification->temp_block_time)->diffInSeconds();

                    Toastr::error(translate('too_many_attempts. please_try_again_after_').CarbonInterval::seconds($time)->cascade()->forHumans());

                }else{
                    $verification->otp_hit_count += 1;
                    $verification->save();

                    Toastr::error(translate('Verification code/ OTP mismatched'));
                }
            }else{
                Toastr::error(translate('Verification code/ OTP mismatched'));
            }

            return redirect()->back();
        }
    }

    // Customer Ajax Verify
    public static function ajax_verify(Request $request)
    {
        Validator::make($request->all(), [
            'token' => 'required',
        ]);

        $email_status = Helpers::get_business_settings('email_verification');
        $phone_status = Helpers::get_business_settings('phone_verification');

        $user = User::find($request->id);
        $verify = PhoneOrEmailVerification::where(['phone_or_email' => $user->email, 'token' => $request['token']])->first();

        $max_otp_hit = Helpers::get_business_settings('maximum_otp_hit') ?? 5;
        $temp_block_time = Helpers::get_business_settings('temporary_block_time') ?? 5; //minute

        if (isset($verify)) {
            if(isset($verify->temp_block_time ) && Carbon::parse($verify->temp_block_time)->diffInSeconds() <= $temp_block_time){
                $time = $temp_block_time - Carbon::parse($verify->temp_block_time)->diffInSeconds();

                $verify_status = 'error';
                $message = translate('please_try_again_after_').CarbonInterval::seconds($time)->cascade()->forHumans();
                return response()->json([
                    'status'=>$verify_status,
                    'message'=>$message,
                ]);
            }

            ($email_status == 1 || ($phone_status == '0' && $email_status == '0')) ? ($user->is_email_verified = 1) : ($user->is_phone_verified = 1);
            $user->save();
            $verify->delete();

            $verify_status = 'success';
            $message = translate('verification_done_successfully');

        }else{
            $verification = PhoneOrEmailVerification::where(['phone_or_email' => $user->email])->first();

            if($verification){
                if(isset($verification->temp_block_time) && Carbon::parse($verification->temp_block_time)->diffInSeconds() <= $temp_block_time){
                    $time= $temp_block_time - Carbon::parse($verification->temp_block_time)->diffInSeconds();

                    $verify_status = 'error';
                    $message = translate('please_try_again_after_').CarbonInterval::seconds($time)->cascade()->forHumans();

                }elseif($verification->is_temp_blocked == 1 && isset($verification->created_at) && Carbon::parse($verification->created_at)->diffInSeconds() >= $temp_block_time){
                    $verification->otp_hit_count = 1;
                    $verification->is_temp_blocked = 0;
                    $verification->temp_block_time = null;
                    $verification->updated_at = now();
                    $verification->save();

                    $verify_status = 'error';
                    $message = translate('Verification code/ OTP mismatched');

                }elseif($verification->otp_hit_count >= $max_otp_hit && $verification->is_temp_blocked == 0){
                    $verification->is_temp_blocked = 1;
                    $verification->temp_block_time = now();
                    $verification->updated_at = now();
                    $verification->save();

                    $time= $temp_block_time - Carbon::parse($verification->temp_block_time)->diffInSeconds();
                    $verify_status = 'error';
                    $message = translate('too_many_attempts. please_try_again_after_').CarbonInterval::seconds($time)->cascade()->forHumans();

                }else{
                    $verification->otp_hit_count += 1;
                    $verification->save();

                    $verify_status = 'error';
                    $message = translate('Verification code/ OTP mismatched');
                }
            }else{
                $verify_status = 'error';
                $message = translate('Verification code/ OTP mismatched');
            }
        }

        return response()->json([
            'status'=>$verify_status,
            'message'=>$message,
        ]);
    }

    public static function login_process($user, $email, $password)
    {
        if (auth('customer')->attempt(['email' => $email, 'password' => $password], true)) {
            $wish_list = Wishlist::whereHas('wishlistProduct',function($q){
                return $q;
            })->where('customer_id', $user->id)->pluck('product_id')->toArray();

            session()->put('wish_list', $wish_list);
            $company_name = BusinessSetting::where('type', 'company_name')->first();
            $message = translate('welcome_to') .' '. $company_name->value . '!';
            CartManager::cart_to_db();
        } else {
            $message = 'Credentials are not matched or your account is not active!';
        }

        return $message;
    }

    // Resend OTP Code Using Ajax
    public static function resend_otp(Request $request)
    {
        $user = User::find($request->user_id);
        $token = PhoneOrEmailVerification::where('phone_or_email','=', $user->email)->first();
        $otp_resend_time = Helpers::get_business_settings('otp_resend_time') > 0 ? Helpers::get_business_settings('otp_resend_time') : 0;

        // Time Difference in Minutes
        if($token){
            $token_time = Carbon::parse($token->created_at);
            $add_time = $token_time->addSeconds($otp_resend_time);
            $time_differance = $add_time > Carbon::now() ? Carbon::now()->diffInSeconds($add_time) : 0;
        }else{
            $time_differance = 0;
        }

        $new_token_generate = rand(1000, 9999);
        if($time_differance==0){
            if($token){
                $token->token = $new_token_generate;
                $token->otp_hit_count = 0;
                $token->is_temp_blocked = 0;
                $token->temp_block_time = null;
                $token->created_at = now();
                $token->save();
            }else{
                $new_token = new PhoneOrEmailVerification();
                $new_token->phone_or_email = $user->email;
                $new_token->token = $new_token_generate;
                $new_token->created_at = now();
                $new_token->updated_at = now();
                $new_token->save();
            }

            $phone_verification = Helpers::get_business_settings('phone_verification');
            $email_verification = Helpers::get_business_settings('email_verification');
            if ($phone_verification && !$user->is_phone_verified) {

                $published_status = 0;
                $payment_published_status = config('get_payment_publish_status');
                if (isset($payment_published_status[0]['is_published'])) {
                    $published_status = $payment_published_status[0]['is_published'];
                }

                if($published_status == 1){
                    SMS_module::send($user->phone, $new_token_generate);
                }else{
                    SmsGateway::send($user->phone, $new_token_generate);
                }
            }

            if ($email_verification && !$user->is_email_verified) {
                $email_services_smtp = Helpers::get_business_settings('mail_config');
                if ($email_services_smtp['status'] == 0) {
                    $email_services_smtp = Helpers::get_business_settings('mail_config_sendgrid');
                }
                if ($email_services_smtp['status'] == 1) {
                    try{
                        Mail::to($user->email)->send(new \App\Mail\EmailVerification($new_token_generate));
                    } catch (\Exception $exception) {
                        return response()->json([
                            'status'=>"0",
                        ]);
                    }
                }
            }
            return response()->json([
                'status'=>"1",
                'new_time'=> $otp_resend_time,
            ]);
        } else {
            return response()->json([
                'status'=>"0",
            ]);
        }
    }

}
