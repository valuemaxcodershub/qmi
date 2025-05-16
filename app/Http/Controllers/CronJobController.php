<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Model\PaymentRequest;
use App\Model\WithdrawRequest;
use Illuminate\Support\Facades\Log;
use App\Services\FlutterwaveHandler;

class CronJobController extends Controller
{
    public function syncWithdrawals() {
        WithdrawRequest::where(['approved' => '0'])->chunk(10, function($withdrawals) {
            if (count($withdrawals) > 0 ) {    
                foreach($withdrawals as $withdrawal) {
                    $reference = $withdrawal['reference'];
                    // $reference = date('Ymd') * mt_rand(1111, 9909);
                    $withdrawalField = json_decode($withdrawal->withdrawal_method_fields, true);
                    $withdrawData = [
                        'amount' => (double) $withdrawal->amount,
                        'account_bank' => $withdrawalField['bank_code'],
                        'account_number' => $withdrawalField['account_number'],
                        'narration' => 'PAVI Withdrawal',
                        'currency' => 'NGN',
                        'reference' => $reference
                    ]; 

                    $flutterwave = new FlutterwaveHandler(new PaymentRequest, new User);
                    $processPayment = $flutterwave->initiateTransfer($withdrawData);

                    $decodeResponse = json_decode((string) $processPayment, true);
                    $flwId = $decodeResponse['data']['id'];

                    WithdrawRequest::where(['approved' => '0', 'reference' => $reference])->update([
                        'approved' => 2, 'memo' => $processPayment, 'external_reference' => $flwId
                    ]);
                }
            }
        });
    }

    public function verifyWithdrawal() {
        WithdrawRequest::where(['approved' => '2'])->whereNotNull('external_reference')->chunk(10, function($withdrawals) {
            if (count($withdrawals) > 0 ) {    
                foreach($withdrawals as $withdrawal) {
                    $flwReference = $withdrawal['external_reference'];

                    $flutterwave = new FlutterwaveHandler(new PaymentRequest, new User);
                    $retrieveTF = $flutterwave->verifyTransfer($flwReference);

                    Log::info($retrieveTF);

                }
            }
        });
    }

}
