<?php 

namespace App\Services;

use Exception;
use App\Models\User;
use App\Model\PaymentRequest;
use Illuminate\Support\Facades\DB;

class WalletService {

    public function updateWallet($referenceId, $paymentStatus = 'successful') {
        try {
            switch($paymentStatus) {
                case 'successful':
                    $getPayment = PaymentRequest::where(['transaction_id' => $referenceId, 'is_paid' => '0'])->first();
                    if($getPayment) {
                        $payerId = $getPayment->payer_id;
                        $user = User::find($payerId);
                        $currentBalance = (float) $user->wallet_balance;

                        $newWallet = (float) ($currentBalance + $getPayment->payment_amount);
                        
                        DB::beginTransaction();
                        $approveTxn = PaymentRequest::where(['transaction_id' => $referenceId, 'is_paid' => '0'])
                                        ->update(['is_paid' => '1']);
                        if($approveTxn) {
                            $updateWallet = User::where(['id' => $payerId])
                                                        ->update(['wallet_balance' => $newWallet]);
                            
                            if($updateWallet) {
                                DB::commit();
                                return response()->json(['message' => 'Payment approved successfully'], 200);
                            }
                            DB::rollBack();
                            return response()->json(['message' => 'Error updating wallet'], 400);
                        }
                        DB::rollBack();
                        return response()->json(['message' => 'Error updating wallet'], 400);
                    }
                    return response()->json(['message' => 'Transaction already approved or does not exists'], 400);
                break;
                
                case 'abandoned':
                case 'cancelled':
                    PaymentRequest::where(['transaction_id' => $referenceId, 'is_paid' => '0'])->update(['is_paid' => '2']);
                    return response()->json(['message' => 'Payment cancelled successfully'], 400);
                break;
            }
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }
}