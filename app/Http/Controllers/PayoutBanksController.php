<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PayoutBanks;
use App\Model\PaymentRequest;
use App\Services\FlutterwaveHandler;
use Illuminate\Support\Facades\Session;

class PayoutBanksController extends Controller
{
    public function payoutBanks() {
        $allBanks = PayoutBanks::orderBy('bank_name', 'asc')->get();
        return view('admin-views.business-settings.payment-method.banks', compact('allBanks'));
    }

    public function fetchBanks() {
        $flutterwave = new FlutterwaveHandler(new PaymentRequest, new User);
        $allBanks = $flutterwave->getAllBanks();

        $allBanks = $flutterwave->getAllBanks();
        if ($allBanks != NULL) {
            $decodeBanks = json_decode($allBanks, true);
            if (strtolower($decodeBanks['status']) == 'success') {
                if (isset($decodeBanks['data'])) {
                    $payoutBanks = $decodeBanks['data'];
                    PayoutBanks::truncate();
                    foreach ($payoutBanks as $bankIndex => $bankInfo) {
                        PayoutBanks::create([
                            'bank_name' => $bankInfo['name'],
                            'bank_code' => $bankInfo['code'],
                        ]);
                    }
                    Session::flash('success', 'Payout banks updated successfully');
                    return redirect()->route('admin.payment-method.list-payout-banks');
                }
                Session::flash('error', 'No payout banks retrieved from Flutterwave');
                return redirect()->route('admin.payment-method.list-payout-banks');
            }
            Session::flash('error', 'Something went wrong from Flutterwave');
            return redirect()->route('admin.payment-method.list-payout-banks');
        }
        Session::flash('error', 'Error connecting to Flutterwave');
        return redirect()->route('admin.payment-method.list-payout-banks');
    }
}
