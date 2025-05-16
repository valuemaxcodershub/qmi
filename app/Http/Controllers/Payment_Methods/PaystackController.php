<?php

namespace App\Http\Controllers\Payment_Methods;

use App\Model\PaymentRequest;
use App\Models\User;
use App\Traits\Processor;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Unicodeveloper\Paystack\Facades\Paystack;

class PaystackController extends Controller
{
    use Processor;

    private PaymentRequest $payment;
    private $user;

    public function __construct(PaymentRequest $payment, User $user)
    {
        $config = $this->payment_config('paystack', 'payment_config');
        $values = false;
        if (!is_null($config) && $config->mode == 'live') {
            $values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $values = json_decode($config->test_values);
        }

        if ($values) {
            $config = array(
                'publicKey' => env('PAYSTACK_PUBLIC_KEY', $values->public_key),
                'secretKey' => env('PAYSTACK_SECRET_KEY', $values->secret_key),
                'paymentUrl' => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),
                'merchantEmail' => env('MERCHANT_EMAIL', $values->merchant_email),
            );
            Config::set('paystack', $config);
        }

        $this->payment = $payment;
        $this->user = $user;
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $this->payment::where(['id' => $request['payment_id'], 'reference' => null])->update([
           'reference' => Paystack::genTranxRef()
        ]);

        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        
        if (!isset($data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }

        $reference = $data->reference;

        $payer = json_decode($data['payer_information']);

        return view('payment.paystack', compact('data', 'payer', 'reference'));
    }

    public function redirectToGateway(Request $request)
    {
        return Paystack::getAuthorizationUrl()->redirectNow();
    }

    public function handleGatewayCallback(Request $request)
    {
        $paymentDetails = Paystack::getPaymentData();
        $paymentReference = $paymentDetails['data']['reference'];

        $updateFields = [
            'payment_method' => 'paystack',
            'is_paid' => 1,
            'transaction_id' => $paymentDetails['data']['id'],
        ];

        if ($paymentDetails['status'] == true) {
            $this->payment::where('reference', $paymentReference)
                ->where('is_paid', 0)
                ->limit(1)
                ->update($updateFields);
            $data = $this->payment::where('reference', $paymentReference)->first();
            if ($data && function_exists($data->success_hook)) {
                call_user_func($data->success_hook, $data);
            }
        } else {
            $payment_data = $this->payment::where('reference', $paymentReference)->first();
            if ($payment_data && function_exists($payment_data->failure_hook)) {
                call_user_func($payment_data->failure_hook, $payment_data);
            }
        }

        return $this->payment_response(
            $paymentDetails['status'] ? $data : $payment_data,
            $paymentDetails['status'] ? 'success' : 'fail'
        );
    }
}
