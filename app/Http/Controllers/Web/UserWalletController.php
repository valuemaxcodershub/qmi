<?php

namespace App\Http\Controllers\Web;

use Carbon\Carbon;
use App\CPU\Helpers;
use App\Models\User;
use App\Model\Setting;
use Illuminate\Http\Request;
use App\Model\PaymentRequest;

use App\Model\WalletTransaction;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;

use App\Model\AddFundBonusCategories;
use App\Services\PaystackHandler;
use App\Services\UtilityService;
use App\Services\FlutterwaveHandler;

use function App\CPU\payment_gateways;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class UserWalletController extends Controller
{

    public function index(Request $request)
    {
        $wallet_status = Helpers::get_business_settings('wallet_status');
        if ($wallet_status == 1) {
            $total_wallet_balance = auth('customer')->user()->wallet_balance;
            $wallet_transactio_list = WalletTransaction::where('user_id', auth('customer')->id())
                ->when($request->has('type'), function ($query) use ($request) {
                    $query->when($request->type == 'order_transactions', function ($query) {
                        $query->where('transaction_type', 'order_place');
                    })->when($request->type == 'converted_from_loyalty_point', function ($query) {
                        $query->where('transaction_type', 'loyalty_point');
                    })->when($request->type == 'added_via_payment_method', function ($query) {
                        $query->where(['transaction_type' => 'add_fund', 'reference' => 'add_funds_to_wallet']);
                    })->when($request->type == 'add_fund_by_admin', function ($query) {
                        $query->where(['transaction_type' => 'add_fund_by_admin']);
                    })->when($request->type == 'order_refund', function ($query) {
                        $query->where(['transaction_type' => 'order_refund']);
                    });
                })->latest()->paginate(10);

            $payment_gateways = payment_gateways();

            $add_fund_bonus_list = AddFundBonusCategories::where('is_active', 1)
                ->whereDate('start_date_time', '<=', date('Y-m-d'))
                ->whereDate('end_date_time', '>=', date('Y-m-d'))
                ->get();

            if ($request->has('flag') && $request->flag == 'success') {
                Toastr::success(translate('add_fund_to_wallet_success'));
                return redirect()->route('wallet');
            } else if ($request->has('flag') && $request->flag == 'fail') {
                Toastr::error(translate('add_fund_to_wallet_unsuccessful'));
                return redirect()->route('wallet');
            }

            return view(VIEW_FILE_NAMES['user_wallet'], compact('total_wallet_balance', 'wallet_transactio_list', 'payment_gateways', 'add_fund_bonus_list'));
        } else {
            Toastr::warning(\App\CPU\translate('access_denied!'));
            return back();
        }
    }

    public function my_wallet_account()
    {
        return view(VIEW_FILE_NAMES['wallet_account']);
    }

    public function fundWalletView() {
        $total_wallet_balance = auth('customer')->user()->wallet_balance;
        $paymentGateways = [];
        
        $paystackData = Setting::where(['key_name' => 'paystack'])->first();

        $flutterwaveData = Setting::where(['key_name' => 'flutterwave'])->first();

        // If flutterwave or paystack is enabled
        $paystackData['is_active'] = $flutterwaveData['is_active'] = 1;
        if($paystackData['is_active'] != 0) {
            $paymentGateways['paystack'] = $paystackData['mode'] == 'test' ? 'Paystack Test' : 'Paystack';
        }

        if($flutterwaveData['is_active'] != 0) {
            $paymentGateways['flutterwave'] = $flutterwaveData['mode'] == 'test' ? 'Flutterwave Test' : 'Flutterwave';
        }
        
        if($paymentGateways == NULL) {
            Session::flash('payment_provider_error', "There is no active Payment Processor at the moment. Please check back later");
        }

        return view(VIEW_FILE_NAMES['fundwallet_view'], compact('total_wallet_balance', 'paymentGateways'));
    }

    public function handleWalletRequest(Request $request) {
        $user = Auth::guard('customer')->user();
        
        $validator = Validator::make($request->all(), [
            "amount" => "numeric|required",
            "payment_gateway" => "string|required"
        ]);

        $amountFund = (float) $request->amount;
        $paymentChannel = $request->payment_gateway;

        if($validator->fails()) {
            return redirect()->route('fundwallet-view')
                            ->withErrors($validator)->withInput();
        }

        $walletBalance = (float) ($user->wallet_balance * session('currency_exchange_rate'));

        $leftOverFunding = (float) (500000 - $walletBalance);

        if($leftOverFunding == 0) {
            Session::flash('error', "Maximum wallet funding limit of ₦500,000 reached. Please make a purchase and try again later");
            return redirect()->back();
        }
        if($amountFund >= $leftOverFunding) {
            Session::flash('error', "Attempt Failed! You can only fund exact or below ₦".number_format($leftOverFunding, 2));
            return redirect()->back();
        }

        $paymentReference = app(UtilityService::class)->uniqueReference();

        $createPayment = PaymentRequest::create([
            'payer_id' => $user->id,
            'payment_amount' => $amountFund,
            'transaction_id' => $paymentReference,
            'currency_code' => '#',
            'payment_method' => $paymentChannel,
            'payer_information' => json_encode(['name' => $user->f_name.' '.$user->l_name, 'phone' => $user->phone, 'email' => $user->email])
        ]);
        
        if($createPayment) {
            switch($paymentChannel) {
                case "paystack":
                    $paystack = new PaystackHandler(new PaymentRequest, new User);
                    $getLink = $paystack->generatePaymentLink($paymentReference);
                break;
                
                case "flutterwave":
                    $flutterwave = new FlutterwaveHandler(new PaymentRequest, new User);
                    $getLink = $flutterwave->generatePaymentLink($paymentReference);
                break;
            }

            if($getLink !== false) {
                return redirect($getLink);
            }
            Session::flash('error', 'Attempt failed. Error communicating with payment processor');
            return redirect()->back();
        }
        Session::flash('error', 'Error creating payment request at this time');
        return redirect()->back();
    }

    public function ApprovePayment(Request $request, string $processorType) {
        $validator = Validator::make($request->all(), [
            "status" => "string",
            "tx_ref" => "numeric|required",
            "trxref" => "numeric|required",
            "reference" => "numeric|required",
            "transaction_id" => "numeric|sometimes"
        ]);

        if($processorType == 'flutterwave') {
            $flutterwave = new FlutterwaveHandler(new PaymentRequest, new User);

            $approvePayment = $flutterwave->updateTransaction($validator->validated());
            
            $responseCode = $approvePayment->getStatusCode();
            $responseContent = json_decode($approvePayment->content());
            $message = $responseContent->message;

            if($responseCode === 200) {
                Session::flash('success', $message);
                return redirect()->route('fundwallet-view');
            }
            Session::flash('error', $message);
            return redirect()->route('fundwallet-view');
        }
        else if($processorType == 'paystack') {
            $paystack = new PaystackHandler(new PaymentRequest, new User);

            $approvePayment = $paystack->updateTransaction($validator->validated());
            
            $responseCode = $approvePayment->getStatusCode();
            $responseContent = json_decode($approvePayment->content());
            $message = $responseContent->message;

            if($responseCode === 200) {
                Session::flash('success', $message);
                return redirect()->route('fundwallet-view');
            }
            Session::flash('error', $message);
            return redirect()->route('fundwallet-view');

        }
    }   

}