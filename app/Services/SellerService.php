<?php 

namespace App\Services;

use App\Model\Seller;
use App\Model\Product;
use Illuminate\Support\Facades\Auth;

class SellerService {

    public function getSeller($sellerId) {
        $seller = Seller::with(['sellertype', 'shop'])->find($sellerId);
        if($seller) {
            // Total count of activated product for Seller...
            $totalApprovedProduct = Product::where(['user_id' => $sellerId, 'status' => 1])->count();
            $seller->approved_product = $totalApprovedProduct;
            return $seller;
        }
        return false;
    }
}