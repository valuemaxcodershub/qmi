<?php 

namespace App\Services;

use App\Classes\HttpRequest;
use App\Models\User;
use App\Traits\Processor;
use App\Model\PaymentRequest;
use Illuminate\Support\Facades\Config;

class FlutterwaveHandler {
    use Processor;

    private PaymentRequest $payment;
    private $user, $endpoint, $headerParams;

    public function __construct(PaymentRequest $payment, User $user)
    {
        $this->endpoint = 'https://api.flutterwave.com/v3/';
        $config = $this->payment_config('flutterwave', 'payment_config');
        $values = false;
        if (!is_null($config) && $config->mode == 'live') {
            $values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $values = json_decode($config->test_values);
        }

        if ($values) {
            $config = array(
                'publicKey' => env('FLUTTERWAVE_PUBLIC_KEY', $values->public_key),
                'secretKey' => env('FLUTTERWAVE_SECRET_KEY', $values->secret_key),
                'paymentUrl' => env('FLUTTERWAVE_PAYMENT_URL', 'https://api.paystack.co'),
            );
            Config::set('flutterwave', $config);
        }

        $this->payment = $payment;
        $this->user = $user;

        
        $this->headerParams = [
            "Authorization" => "Bearer ".Config::get('flutterwave.secretKey'),
            "Content-Type" => "application/json",
            "Cache-Control" => "no-cache",
        ];
    }

    public function generatePaymentLink($reference) {
        $config = $this->payment_config('flutterwave', 'payment_config');
        $getPayment = PaymentRequest::where(['transaction_id' => $reference, 'is_paid' => '0'])->first();

        if($getPayment != NULL) {
            $paymentAmount = (float) $getPayment->payment_amount;
            $user = User::find($getPayment->payer_id);

            $paymentData = [
                "tx_ref" => $reference,
                "amount" => $paymentAmount,
                "currency" => "NGN",
                "redirect_url" => route('user.approve-payment', ['processorType' => 'flutterwave']),
                "customer" => [
                    "email" => $user->email,
                    "phonenumber" => $user->phone,
                    "name" => $user->f_name. ' '.$user->l_name
                ],
                "customizations" => [
                    "title" => env("APP_NAME"),
                    "logo" => asset('storage/app/public/payment_modules/gateway_image/'.json_decode($config->additional_data)->gateway_image)
                ]
            ];
    
            $reserveResult = HttpRequest::sendPost($this->endpoint.'payments', $paymentData, $this->headerParams);

            // Decode the response gotten....
            $decodeReserve = json_decode((string) $reserveResult, true);

            if($decodeReserve['status'] == "success") {
                $authorizedUrl = $decodeReserve['data']['link'];

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
        $status = $paymentData['status'];
        $referenceId = $paymentData['tx_ref'];

        if($status == "cancelled") {
            return app(WalletService::class)->updateWallet($referenceId, 'cancelled');
        }

        $transaction_id = $paymentData['transaction_id'];
        $verifyPayment = self::verifyPayment($transaction_id, $referenceId);

        if($verifyPayment) {
            return app(WalletService::class)->updateWallet($referenceId, 'successful');
        }
        return response()->json(["message" => "Error approving payment or reference not found"], 400);
    }

    public function verifyPayment($transaction_id, $referenceId) {
        $verifyPayment = HttpRequest::sendGet($this->endpoint."transactions/".$transaction_id."/verify", "", $this->headerParams);

        $decodePayment = json_decode($verifyPayment, true);

        if($decodePayment['status'] === "success") {
            if($decodePayment['data']['status'] === "successful" AND $decodePayment['data']['tx_ref'] == $referenceId) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function getAllBanks() {
        $fetchBanks = HttpRequest::sendGet($this->endpoint."/banks/NG", "", $this->headerParams);
        return $fetchBanks;
    }

    public function verifyBankAccount($bankCode, $accountNumber) {
        // Let's send the request to Flutterwave...
        $accountData = [
            "account_number" => $accountNumber,
            "account_bank" => $bankCode
        ];

        $reserveResult = HttpRequest::sendPost($this->endpoint."/accounts/resolve", $accountData, $this->headerParams);
        return $reserveResult;
    }
    
    public function initiateTransfer($paymentData) {
        // Let's send the request to Flutterwave...
        $reserveResult = HttpRequest::sendPost($this->endpoint."/transfers", $paymentData, $this->headerParams);
        return $reserveResult;
    }

    public function verifyTransfer($flwId) {
        $verifyTransfer = HttpRequest::sendGet($this->endpoint."/transfers/".$flwId, "", $this->headerParams);
        return $verifyTransfer;
    }

}