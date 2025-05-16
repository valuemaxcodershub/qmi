<?php

namespace App\Http\Controllers\Web;

use Carbon\Carbon;
use App\CPU\Helpers;
use App\Models\User;
use Illuminate\Http\Request;
use App\Model\WithdrawRequest;
use App\Services\UtilityService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class WithdrawalController extends Controller
{
    public function index() {
        $theUser = auth('customer')->user();
        $total_wallet_balance = $theUser->wallet_balance;
        if($theUser['bank_detail'] == NULL) {
            Session::flash('error', 'Your banking information is missing. Please update');
            return redirect()->route('user-bank-account');
        }
        $bankDetail = json_decode($theUser['bank_detail'], true);
        return view(VIEW_FILE_NAMES['withdraw_view'], compact('total_wallet_balance', 'bankDetail'));
    }

    public function initiateWithdrawal(Request $request) {
        $request->validate([
            'amount' => 'numeric|required|min:1',
            'transact_pin' => 'numeric|required|min:6',
        ], [
            'amount.required' => 'Please enter a valid amount to withdraw',
            'transact_pin.required' => 'Please enter your transaction pin'
        ]);
        
        $theUser = auth('customer')->user();
        $userBalance = (float) $theUser->wallet_balance;
        $monthWithdrawlLimit = 500000;

        $startDate = Carbon::now()->startOfMonth()->format('Y-m-d 00:00');
        $endDate = Carbon::now()->endOfMonth()->format('Y-m-d 23:59');
        $currentMonthName = Carbon::now()->format('F, Y');
        
        $totalWithdrawal = WithdrawRequest::where(['user_id' => $theUser->id])->whereIn('approved', ['0', '1'])->whereBetween('created_at', [$startDate, $endDate])->sum('amount');

        $leftWithdrawable = $totalWithdrawal > 0 ? ($monthWithdrawlLimit - $totalWithdrawal) : $monthWithdrawlLimit;
        
        $withdrawAmount = (float) $request->amount;
        
        if($theUser['transact_pin'] != $request->transact_pin) {
            Session::flash('error', 'Incorrect transaction pin supplied');
            return redirect()->back();
        } else if($withdrawAmount > $userBalance) {
            Session::flash('error', 'Insufficient wallet balance. Kindly try withdrawing lower than your balance');
            return redirect()->back();
        }
        else if($leftWithdrawable <= 0) {
            Session::flash('error', 'Withdrawal limit exceeded for '.$currentMonthName);
            return redirect()->back();
        }
        else if($withdrawAmount > $leftWithdrawable) {
            Session::flash('error', 'You can only withdraw '.Helpers::currency_converter($leftWithdrawable).' at the moment');
            return redirect()->back();
        }
        else {  

            $createWithdrawal = WithdrawRequest::create([
                'user_id' => $theUser['id'],
                'amount' => $withdrawAmount,
                'reference' => app(UtilityService::class)->uniqueReference(),
                'withdrawal_method_fields' => $theUser->bank_detail
            ]); 
            if(!$createWithdrawal) {
                Session::flash('error', 'Something went wrong processing your request');
                return redirect()->back();
            }
            User::where('id' , $theUser->id)->update([
                "wallet_balance" => $userBalance - $withdrawAmount
            ]);
            Session::flash('success', 'Withdrawal request was successful');
            return redirect()->back();
        }
    }
}