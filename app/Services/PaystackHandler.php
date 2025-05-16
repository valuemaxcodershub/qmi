<?php 

namespace App\Services;

use App\Classes\HttpRequest;
use App\Models\User;
use App\Traits\Processor;
use App\Model\PaymentRequest;
use App\Model\Seller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class PaystackHandler {
    use Processor;

    private PaymentRequest $payment;
    private $user, $endpoint, $headerParams;

    public function __construct(PaymentRequest $payment, User|Seller $user)
    {
        $this->endpoint = 'https://api.paystack.co/transaction/';
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
            );
            Config::set('paystack', $config);
        }

        $this->payment = $payment;
        $this->user = $user;

        
        $this->headerParams = [
            "Authorization" => "Bearer ".Config::get('paystack.secretKey'),
            "Content-Type" => "application/json",
            "Cache-Control" => "no-cache",
        ];
    }

    public function generatePaymentLink($reference, $payerType = 'user') {
        $config = $this->payment_config('paystack', 'payment_config');
        $getPayment = PaymentRequest::where(['transaction_id' => $reference, 'is_paid' => '0'])->orWhere('reference', $reference)->first();

        if($getPayment != NULL) {
            $paymentAmount = (float) $getPayment->payment_amount;
            if ($payerType == 'user') {
                $user = User::find($getPayment->payer_id);
    
                $paymentData = [
                    "reference" => $reference,
                    "amount" => (float) $paymentAmount * 100,
                    "currency" => "NGN",
                    "callback_url" => route('user.approve-payment', ['processorType' => 'paystack']),
                    "email" => $user->email,
                    "phonenumber" => $user->phone,
                    "name" => $user->f_name. ' '.$user->l_name,
                    "customizations" => [
                        "title" => env("APP_NAME"),
                        "logo" => asset('storage/app/public/payment_modules/gateway_image/'.json_decode($config->additional_data)->gateway_image)
                    ],
                    "channels" => ['card', 'bank', 'ussd', 'qr', 'mobile_money', 'bank_transfer']
                ];                
            } else {
                $seller = Seller::find($getPayment->payer_id);
                $paymentData = [
                    "reference" => $reference,
                    "amount" => (float) $paymentAmount * 100,
                    "currency" => "NGN",
                    "email" => $seller->email,
                    "phonenumber" => $seller->phone,
                    "name" => $seller->f_name. ' '.$seller->l_name,
                    "customizations" => [
                        "title" => env("APP_NAME"),
                        "logo" => asset('storage/app/public/payment_modules/gateway_image/'.json_decode($config->additional_data)->gateway_image)
                    ],
                    "channels" => ['card', 'bank', 'ussd', 'qr', 'mobile_money', 'bank_transfer']
                ];  
                $paymentAttribute = strtolower($getPayment->attribute);
                if ($paymentAttribute == 'product-boosting') {
                    $paymentData['callback_url'] = route('seller.shop.approve-boosting-payment-online', ['processorType' => 'paystack']);
                }
            }
            
            $reserveResult = HttpRequest::sendPost($this->endpoint.'initialize', $paymentData, $this->headerParams);

            // Decode the response gotten....
            $decodeReserve = json_decode((string) $reserveResult, true);

            if($decodeReserve['status'] == true) {
                $authorizedUrl = $decodeReserve['data']['authorization_url'];

                if (!filter_var($authorizedUrl, FILTER_VALIDATE_URL)) {
                    return response()->json(['message' => 'Invalid URL'], 422);
                }
                return $authorizedUrl;
            }

            // Reject the payment if it's failing...
            PaymentRequest::where(['transaction_id' => $reference, 'is_paid' => '0'])->update(['is_paid' => '2']);
            return false;
        }
        return false;
    }

    public function updateTransaction($paymentData) {

        $referenceId = $paymentData['reference'];
        $transaction_id = $paymentData['trxref'];

        $verifyPayment = self::verifyPayment($referenceId);

        if($verifyPayment) {
            return app(WalletService::class)->updateWallet($referenceId, 'successful');
        }
        return app(WalletService::class)->updateWallet($referenceId, 'cancelled');
    }

    public function verifyPayment($referenceId) {
        $verifyPayment = HttpRequest::sendGet($this->endpoint."verify/".$referenceId, "", $this->headerParams);
        
        $decodePayment = json_decode($verifyPayment, true);

        if($decodePayment['data']['status']  === "success" AND $decodePayment['data']['reference'] == $referenceId) {
            return true;
        }
        return false;
    }
    
}