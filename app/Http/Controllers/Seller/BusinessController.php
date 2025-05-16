<?php

namespace App\Http\Controllers\Seller;

use App\Models\User;
use App\Model\Seller;
use App\Models\SellerTypes;
use Illuminate\Support\Str;
use App\Rules\NoSingleQuote;
use Illuminate\Http\Request;
use App\Model\PaymentRequest;
use App\Model\BusinessSetting;
use App\Models\BusinessUpgrade;
use App\Services\MonnifyService;
use App\Services\UtilityService;
use App\Mail\SellerUpgradeRequest;
use App\Mail\SellerNINVerification;
use App\Mail\SellerUpgradeApproval;
use App\Mail\SellerUpgradeInReview;
use App\Model\OfflinePaymentMethod;
use App\Http\Controllers\Controller;
use App\Mail\SellerUpgradeRejection;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\BusinessUpgradeRequest;

class BusinessController extends Controller
{
    public function index()
    {
        $seller_data = Seller::with(['sellertype', 'shop'])->withCount('product')->where('id', auth('seller')->id())->first();
        $seller_data->total_product_added = $seller_data->product()->where('status', 1)->count();
        $seller_data->product_status = self::productLimitStatus($seller_data->sellertype->product_limit, $seller_data->total_product_added);
        
        $isPendingUpgrade = BusinessUpgrade::where(['seller_id' => $seller_data->id])->whereIn('status', ['pending', 'review'])->first();
        
        if ($isPendingUpgrade != NULL) {
            Session::flash('error', "You currently have an ongoing upgrade request. One of our representative will attend to you shortly");
        }

        return view('seller-views.business.view', compact('seller_data'));
    }

    private function productLimitStatus($productLimit, $productAdded) {
        if ($productLimit == $productAdded) {
            $status = "<span class='text-danger'>Exceeded</span>";
        } 
        else if ($productLimit > $productAdded) {
            $status = "<span class='text-success'>Available</span>";
        } 
        else {
            $status = "<span class='text-info'>In Progress</span>";
        }
        return $status;
    }

    public function upgradeBusinessView() {
        $seller = Seller::with(['sellertype', 'shop'])->where('id', auth('seller')->id())->first();
        $sellersPackages = SellerTypes::whereNotIn('name', [$seller->sellertype->name, 'Gold'])->get();
        
        $isPendingUpgrade = BusinessUpgrade::where(['seller_id' => $seller->id])->whereIn('status', ['pending', 'review'])->first();
        
        if ($isPendingUpgrade != NULL) {
            Session::flash('error', "You currently have an ongoing upgrade request. Please check back later");
            return redirect()->route('seller.business.profile');
        }
        return view('seller-views.business.upgrade-business', compact('seller', 'sellersPackages'));
    }

    public function businessShortCode() {
        $businessShortcode = auth('seller')->user()->business_shortcode;
        return view('seller-views.business.shortcode', compact('businessShortcode'));
    }

    public function selectMerchantPackage(Request $request) {
        $seller = Seller::with(['sellertype', 'shop'])->where('id', auth('seller')->id())->first();
        $packageType = strtolower($request->merchant_package);

        if($packageType == 'individual') {
            return view('seller-views.business.individual-upgrade', compact('seller'));
        } else {
            return view('seller-views.business.corporate-upgrade', compact('seller'));
        }
    }

    public function processBusinessUpgrade(BusinessUpgradeRequest $request) {
        $sellerId = auth('seller')->id();
        $seller = Seller::with(['sellertype'])->where('id', $sellerId)->first();

        $upgradeData = $request->validated();

        $currentSellerId = isset($seller->sellertype->id) ? $seller->sellertype->id : BusinessSetting::where(['type' => 'default_seller_type'])->first()->value;
        $createUpgrade = false;
        $txReference = Str::random(18);
        $ninSlipFile = "";

        if ($request->file('individual_identity')) {
            $filePath = env('UPLOAD_INDIVIDUAL_FOLDER').date('Y-m-d');            
            $individualIdentity = $request->file('individual_identity');
            $identityFile = rand(101,999)*rand(101,999).'.png';  
            $individualIdentity->storeAs($filePath, $identityFile);

            // Passport upload
            $individualPassport = $request->file('individual_passport');
            $passportFile = rand(101,999)*rand(101,999).'.png';  
            $individualPassport->storeAs($filePath, $passportFile);

            if ($request->file('ninSlip')) {
                $ninSlip = $request->file('ninSlip');
                $ninSlipFile = rand(101,999)*rand(101,999).'.png';  
                $ninSlip->storeAs($filePath, $ninSlipFile);
            }

            $filesData = [
                'passport' => $filePath.'/'.$passportFile,
                'identity' => $filePath.'/'.$identityFile
            ];

            if (!empty($ninSlipFile)) {
                $filesData['nin_slip'] = $filePath.'/'.$ninSlipFile;
            }

            $filesData = json_encode($filesData);

            $createUpgrade = BusinessUpgrade::create([
                "seller_id" => $sellerId,
                "contact_address" => $upgradeData['individual_contact_address'],
                "city" => $upgradeData['individual_city'],
                "lga" => $upgradeData['individual_lga'],
                "current_seller_type" => $currentSellerId,
                "new_seller_type" => SellerTypes::where(['name' => 'Individual'])->first()->id,
                "attachments" => $filesData,
                "reference" => $txReference,
                "status" => "pending"
            ]);
        }

        // Corporate Upgrade...
        if ($request->file('cac_certificate')) {
            $filePath = env('UPLOAD_CORPORATE_FOLDER').date('Y-m-d');   

            // CAC Document...
            $cacCertificate = $request->file('cac_certificate');
            $cacCertFile = rand(101,999)*rand(101,999).'.png';  
            $cacCertificate->storeAs($filePath, $cacCertFile);

            // Professional Body Document...
            $profBodyCertificate = $request->file('professional_body_certificate');
            $profBodyCertFile = rand(101,999)*rand(101,999).'.png';  
            $profBodyCertificate->storeAs($filePath, $profBodyCertFile);

            // Product Registration Number Document...
            $productRegNumber = $request->file('product_reg_number');
            $productRegNumberFile = rand(101,999)*rand(101,999).'.png';  
            $productRegNumber->storeAs($filePath, $productRegNumberFile);

            // Product Registration Number Document...
            $managerIdentity = $request->file('manager_identity');
            $managerIdFile = rand(101,999)*rand(101,999).'.png';  
            $managerIdentity->storeAs($filePath, $managerIdFile);

            // Product Registration Number Document...
            $managerPassport = $request->file('manager_passport');
            $managerPicFile = rand(101,999)*rand(101,999).'.png';  
            $managerPassport->storeAs($filePath, $managerPicFile);

            $taxPaperFile = "";
            if($request->file('tax_paper')) {
                // Product Registration Number Document...
                $taxPaper = $request->file('tax_paper');
                $taxPaperFile = rand(101,999)*rand(101,999).'.png';  
                $taxPaper->storeAs($filePath, $taxPaperFile);
            }

            $filesData = json_encode([
                'cac_certificate' => $filePath.'/'.$cacCertFile,
                'professional_body_certificate' => $filePath.'/'.$profBodyCertFile,
                'product_reg_number' => $filePath.'/'.$productRegNumberFile,
                'manager_identity' => $filePath.'/'.$managerIdFile,
                'manager_passport' => $filePath.'/'.$managerPicFile
            ]);

            if($taxPaperFile != "") {
                $filesData['tax_paper'] = $taxPaperFile;
            }

            $managerDetails = json_encode([
                "name" => $upgradeData['business_manager_name'],
                "phone" => $upgradeData['business_manager_phone'],
                "address" => $upgradeData['business_manager_contact_address']
            ]);

            $createUpgrade = BusinessUpgrade::create([
                "seller_id" => $sellerId,
                "company_name" => $upgradeData['company_name'],
                "company_email" => $upgradeData['company_email'],
                "business_year" => $upgradeData['business_year'],
                "company_phone" => $upgradeData['company_phone'],
                "company_address" => $upgradeData['company_address'],
                "partner_companies" => $upgradeData['partner_companies'],
                "manager_details" => $managerDetails,
                "current_seller_type" => $currentSellerId,
                "new_seller_type" => SellerTypes::where(['name' => 'Corporate'])->first()->id,
                "attachments" => $filesData,
                "reference" => $txReference,
                "status" => "pending"
            ]);
        }

        if(!$createUpgrade) {
            Session::flash('error', "Your request failed. Please try again");
            return redirect()->back();
        }
        
        // Session::flash('success', "Your upgrade request has been received and will be treated accordingly. One of our support representative will be with you shortly");
        return redirect()->route('seller.business.pop-upload', ['reference' => $txReference]);
    }

    public function POPUploadView(Request $request) {
        $reference = $request->query('reference');
        $sellerId = auth('seller')->id();
        $seller = Seller::with(['sellertype', 'shop'])->where('id', $sellerId)->first();
        $findUpgrade = BusinessUpgrade::where(['seller_id' => $sellerId, "status" => "pending", 'reference' =>  $reference])->first();
        if ($findUpgrade == NULL) {
            Session::flash('error', 'You do not have any ongoing upgrade at the moment, kindly start your upgrading process');
            return redirect()->route('seller.business.upgrade-business');
        }
        
        $attachments = json_decode($findUpgrade['attachments'], true);
        $offlinePayments = OfflinePaymentMethod::all();

        $sellerType = SellerTypes::where(['id' => $findUpgrade->new_seller_type])->first();

        if (isset($attachments['payment_receipt'])) {
            Session::flash('error', 'Proof of payment has already been uploaded. Kindly await our response');
            return redirect()->route('seller.business.upgrade-business');
        }
        return view('seller-views.business.uploadPOP', compact('seller', 'offlinePayments', 'sellerType'));
    }

    public function submitPOP(Request $request) {
        $sellerId = auth('seller')->id();
        $reference = $request->input('reference');

        $seller = Seller::where(['id' => $sellerId])->first();
        $findUpgrade = BusinessUpgrade::where(['seller_id' => $sellerId, "status" => "pending", 'reference' =>  $reference])->first();
        $sellerType = SellerTypes::where(['id' => $findUpgrade->new_seller_type])->first();
        $uploadDIR = strtolower($sellerType->name) == 'individual' ? env('UPLOAD_INDIVIDUAL_FOLDER') : env('UPLOAD_CORPORATE_FOLDER');
        $uploadedAttachments = json_decode($findUpgrade['attachments'], true);

        if ($request->file('popupload')) {
            $filePath = $uploadDIR.date('Y-m-d');            
            $popUpload = $request->file('popupload');
            $popUploadFile = rand(101,999)*rand(101,999).'.png';  

            $popUpload->storeAs($filePath, $popUploadFile);
            $uploadedAttachments['payment_receipt'] = $filePath.'/'.$popUploadFile;

            $updateUpgrade = BusinessUpgrade::where(['seller_id' => $sellerId, "status" => "pending", 'reference' =>  $reference])->update([
                'attachments' => json_encode($uploadedAttachments)
            ]);
            
            if(!$updateUpgrade) {
                Session::flash('error', "Your request failed. Please try again");
                return redirect()->back();
            }
        }
        Session::flash('success', "Your upgrade request has been received and will be treated accordingly. One of our support representative will be with you shortly");
        Mail::to($seller->email)->send(new SellerUpgradeRequest($seller, $sellerType));
        return redirect()->route('seller.business.upgrade-business');
    }

    public function updateStoreName(Request $request) {
        $validator = Validator::make($request->all(), [
            'store_name' => ['required', 'string', new NoSingleQuote]
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $data = $validator->validated();
        $storeName = str_replace(" ", "", $data['store_name']);

        $isExist = Seller::where(['business_shortcode' => $storeName])->first();
        
        if($isExist != NULL AND $isExist->id != auth('seller')->id()) {
            Session::flash('error', "Store name ($storeName) already belongs to another store");
            return redirect()->back()->withInput();
        }

        $updateId = Seller::where(['id' => auth('seller')->id()])->update(['business_shortcode' => $storeName]);
        
        if($updateId) {
            Session::flash('success', "Store name ($storeName) updated successfully");
            return redirect()->back();
        }
        
        Session::flash('error', "Error updating account");
        return redirect()->back()->withInput();

    }

    public function SellersUpgradeRequest() {
        $sellerUpgrades = BusinessUpgrade::with(['seller'])->select('business_upgrades.*', 'current_seller_type.name as current_seller_type_name', 'new_seller_type.name as new_seller_type_name')
                                        ->leftJoin('seller_types as current_seller_type', 'business_upgrades.current_seller_type', '=', 'current_seller_type.id')
                                        ->leftJoin('seller_types as new_seller_type', 'business_upgrades.new_seller_type', '=', 'new_seller_type.id')
                                        ->orderBy('id', 'desc')->paginate(20);

        $sellerUpgrades->map(function ($upgrade) {
            $upgrade->statusHtml = app(UtilityService::class)->statusBadge($upgrade->status);
            return $upgrade;
        });

        return view('admin-views.seller.seller-upgrade-request', compact('sellerUpgrades'));
    }

    public function viewUpgradeRequest(Request $request, $reference) {
        $viewUpgrade = BusinessUpgrade::with(['seller.sellertype', 'sellertype', 'currentsellertype'])->where(['reference' => $reference])->first();
        
        if ($viewUpgrade == NULL) {
            return redirect()->route('admin.sellers.seller-upgrade-request');
        }
        
        $newPackage = strtolower($viewUpgrade->sellertype->name);
        $viewUpgrade->statusHtml = app(UtilityService::class)->statusBadge($viewUpgrade->status);

        if($newPackage == 'individual') {
            return view('admin-views.seller.view-ind-upgrade-request', compact('viewUpgrade'));
        } else {
            return view('admin-views.seller.view-coop-upgrade-request', compact('viewUpgrade'));
        }
    }

    public function submitUpgradeRequest(Request $request, $reference, $status) {   
        $findUpgrade = BusinessUpgrade::with(['sellertype', 'seller.sellertype'])
                        ->where(['reference' => $reference])
                        ->whereNotIn('status', ['approved', 'rejected'])->first();
                                
        if ($findUpgrade == NULL) {
            Session::flash('error', 'Business upgrade request could not be found or already treated');
            return redirect()->route('admin.sellers.seller-upgrade-request');
        }
        $seller = $findUpgrade->seller;
        $sellerType = $findUpgrade->sellertype;
        
        $upgradePackage = [
            'old_package' => [
                'name' => $seller->sellertype->name,
                'color' => $seller->sellertype->rank_color,
                'product_limit' => $seller->sellertype->product_limit
            ],
            'new_package' => [
                'name' => $sellerType->name,
                'color' => $sellerType->rank_color,
                'product_limit' => $sellerType->product_limit
            ]
        ];        

        if ($status == 'review') {
            BusinessUpgrade::where(['reference' => $reference])->update([
                'status' => strtolower($status)
            ]);
            Mail::to($seller->email)->send(new SellerUpgradeInReview($seller, $upgradePackage));
            Session::flash('success', 'Upgraded updated to review status');
        }
        else if ($status == 'approved') {
            
            BusinessUpgrade::where(['reference' => $reference])->update([
                'status' => strtolower($status)
            ]);
            Seller::where(['id' => $seller->id])->update(['seller_type' => $findUpgrade->new_seller_type]);
            Mail::to($seller->email)->send(new SellerUpgradeApproval($seller, $upgradePackage));
            Session::flash('success', 'Seller upgraded successfully');
        }
        return redirect()->route('admin.sellers.seller-upgrade-request');
    }

    public function declineUpgradeRequest(Request $request, $reference) {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string'
        ]);
        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $findUpgrade = BusinessUpgrade::with(['sellertype', 'seller.sellertype'])
                        ->where(['reference' => $reference])
                        ->whereNotIn('status', ['approved', 'rejected'])->first();
        
        if ($findUpgrade == NULL) {
            Session::flash('error', 'Business upgrade request could not be found or already treated');
            return redirect()->route('admin.sellers.seller-upgrade-request');
        }
        $seller = $findUpgrade->seller;

        $data = $validator->validated();
        $seller = $findUpgrade->seller;
        BusinessUpgrade::where(['reference' => $reference])->update([
            'status' => 'rejected'
        ]);
        Mail::to($findUpgrade->seller->email)->send(new SellerUpgradeRejection($seller, $data));
        Session::flash('success', 'Seller upgrade rejected successfully');
        return redirect()->route('admin.sellers.seller-upgrade-request');
    }

    public function updateSellerNin($reference, $action) {
        $getUpgrade = BusinessUpgrade::where(['reference' => $reference])->first();
        if ($getUpgrade) {
            $sellerId = $getUpgrade->seller_id;
            $getSeller = seller::where(['id' => $sellerId, 'is_nin_verified' => '0'])->first();
            switch ($action) {
                case 'approve':
                    $updateNIN = seller::where(['id' => $sellerId, 'is_nin_verified' => '0'])->update(['is_nin_verified' => '1']);
                    Toastr::success('KYC Verification approved successfully');
                break;
                case 'decline':
                    $updateNIN = false;
                    Toastr::error('NIN Verification failed');
                break;
            }
            Mail::to($getSeller->email)->send(new SellerNINVerification($getSeller, $action));
            return redirect()->back();
        }
        Session::flash('error', 'Error retrieving upgrade request');
        return redirect()->back();
    }

    public function kycVerify() {
        return view('admin-views.seller.kyc');
    }

    public function verifyNIN(Request $request) {
        $request->validate([
            'ninNumber' => 'required|numeric',
            'email' => 'required|email'
        ]);    
        
        $sellerEmail = $request->email;
        $getSeller = Seller::where(['email' => $sellerEmail])->first();
        if (!$getSeller) {
            Session::flash('error', "Seller ($sellerEmail) Not found");
            return redirect()->back()->withInput();
        }

        $sellerName = $getSeller->f_name . ' '. $getSeller->l_name;

        if ($getSeller->is_nin_verified == '1') {
            Session::flash('error', "Seller ($sellerName : $sellerEmail) NIN already verified");
            return redirect()->back()->withInput();
        }
        
        $ninNumber = $request->input('ninNumber');
        $monnifyService = new MonnifyService(new PaymentRequest, new User);
        // $result = $monnifyService->verifyNIN($ninNumber);
        $result = '{"requestSuccessful":true,"responseMessage":"success","responseCode":"0","responseBody":{"nin":"19640146026","lastName":"OGUNDOWOLE","firstName":"RAHEEM","middleName":"OPEYEMI","dateOfBirth":"1995-10-18","gender":"MALE","mobileNumber":"08179653448"}}';

        $decodeResponse = json_decode($result, true);
        if (isset($decodeResponse['requestSuccessful']) AND strtolower($decodeResponse['responseMessage']) == "success") {
            $firstName = isset($decodeResponse['responseBody']['firstName']) ? $decodeResponse['responseBody']['firstName'] : "";
            $lastName = isset($decodeResponse['responseBody']['lastName']) ? $decodeResponse['responseBody']['lastName'] : "";
            $middleName = isset($decodeResponse['responseBody']['middleName']) ? $decodeResponse['responseBody']['middleName'] : "";
            $ninName = $lastName . " ". $firstName . " ". $middleName;

            $isNameMatch = app(UtilityService::class)->nameMatch($sellerName, $ninName);    

            if ($isNameMatch == "NO_MATCH") {
                Session::flash('error', "NIN Name ($ninName) does not match user credentials ($sellerName)");
                return redirect()->back()->withInput();
            }

            $updateKyc = Seller::where(['email' => $sellerEmail])->update([
                "is_nin_verified" => "1"
            ]);

            // $updateKyc = true;

            if ($updateKyc) {
                $decodeResponse['user'] = $getSeller;
                Session::flash('success', "Seller ($sellerName : $request->email) verified successfully");
                Session::flash('info', $decodeResponse);
                Mail::to($sellerEmail)->send(new SellerNINVerification($getSeller, 'approve'));
                return redirect()->back()->withInput();
                return redirect()->back();
            } else {
                Session::flash('error', "Error updating seller kyc status");
                return redirect()->back()->withInput();
            }

        } 
        Session::flash('error', "Error verifying NIN Number ($ninNumber)");
        Mail::to($sellerEmail)->send(new SellerNINVerification($getSeller, 'decline'));
        return redirect()->back()->withInput();
    }

}
