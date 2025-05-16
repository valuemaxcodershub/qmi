<?php 

namespace App\Services;

use App\Model\BusinessSetting;
use App\Models\SellerTypes;

class UtilityService {

    public function translateOrderStatus($status, $isBadge = false) {
        $status = strtolower($status);
        // return $status;
        if(str_contains($status, "pending")) {
            return $isBadge === false ? "Order Received" : "<span class='badge bg-info badge-sm text-white p-2'>Order Received</span>";
        } else if(str_contains($status, "confirmed")) {
            return $isBadge === false ? "Order Confirmed" : "<span class='badge bg-success badge-sm text-white p-2'>Order Confirmed</span>";
            return "<b>$status</b>";
        } else if (str_contains($status, "processing")) {
            return $isBadge === false ? "Order is in Packaging state" : "<span class='badge bg-info badge-sm text-white p-2'>Order is in Packaging state</span>";       
        } else if (str_contains($status, "lodge")) {
            return $isBadge === false ? "Log for complaint" : "<span class='badge bg-warning badge-sm text-white p-2'>Log for complaint</span>";       
        } else if (str_contains($status, "out_for_delive")) {
            return $isBadge === false ? "Order is out for Delivery" : "<span class='badge bg-dark badge-sm text-white p-2'>Shipped</span>";       
        } else if(str_contains($status, "delivered")) {
            return $isBadge === false ? "Order Delivered" : "<span class='badge bg-success badge-sm text-white p-2'>Order Delivered</span>";
        } else if (str_contains($status, "returned")) {
            return $isBadge === false ? "Order Returned" : "<span class='badge bg-danger badge-sm text-white p-2'>Order Returned</span>";       
        } else if(str_contains($status, "failed")) {
            return $isBadge === false ? "Failure to deliver order, Refunded" : "<span class='badge bg-danger badge-sm text-white p-2'>Failure to deliver order, Refunded</span>";
        } else {
            return $isBadge === false ? "Order Canceled" : "<span class='badge bg-danger badge-sm text-white p-2'>Order Canceled</span>";       
        }
    }

    public function competitionStatus(int $status, $isBadge = false) {
        if ($status == 1) {
            return $isBadge === false ? "Ongoing Competition" : "<span class='d-flex badge bg-success badge-sm text-white p-2 m-1'>Ongoing Competition</span>";
        } else if ($status == 0) {
            return $isBadge === false ? "Competition Stopped" : "<span class='d-flex badge bg-dark badge-sm text-white p-2 m-1'>Competition Disabled</span>";
        }
    }

    public function getSellerType($sellerTypeId, $showText = false) {
        
        if($sellerTypeId == NULL) {
            $defaultSeller = BusinessSetting::where(['type' => 'default_seller_type'])->first();
            $sellerTypeId = $defaultSeller->value;
        }

        $fetchType = SellerTypes::find($sellerTypeId);
        
        if($fetchType != NULL) {
            if (!str_contains(strtolower($fetchType->name) , 'free')) {
                $colorCode = strtolower($fetchType->rank_color);
                $titleName = !str_contains(strtolower($fetchType->name), 'merchant') ? $fetchType->name ." Merchant Verified" : $fetchType->name ." Verified" ;
                if ($showText) {
                    return "<br> <span style='font-size: 12px; color: $colorCode' title='$titleName'>
                                <span class='material-symbols-outlined'style='font-size: 12px;'>
                                    verified
                                </span> 
                                <small class=''>Verified</small>
                            </span>";
                } else {
                    return "<span style='color: $colorCode' title='$titleName'>
                                <span class='material-symbols-outlined'>
                                    verified
                                </span> 
                            </span>";
                }
            }
        }
        return ;
    }

    public function uniqueReference() {
        return date("YmdHi").random_int(100, 1000);
    }

    public function statusBadge($status) {
        switch (strtolower($status)) {
            case '0':
            case 'pending':
                $statusHtml = "<span class='badge badge-primary badge-sm text-white p-2'>Pending</span>";
            break;

            case 'review':
                $statusHtml = "<span class='badge badge-warning badge-sm text-white p-2'>Under Review</span>";
            break;
            
            case 'success':
            case 'successful':
            case 'approved':
                $statusHtml = "<span class='badge badge-success badge-sm text-white p-2'>".ucfirst($status)."</span>";
            break;
            
            case '1':
                $statusHtml = "<span class='badge badge-success badge-sm text-white p-2'>Approved</span>";
            break;
            
            case 'rejected':
            case 'cancelled':
                $statusHtml = "<span class='badge badge-danger badge-sm text-white p-2'>".ucfirst($status)."</span>";
            break;
            
            case '2':
                $statusHtml = "<span class='badge badge-danger badge-sm text-white p-2'>Failed</span>";
            break;

        }
        return $statusHtml;
    }

    public static function nameMatch($name1, $name2) {
        // Split the names into parts
        $parts1 = explode(" ", $name1);
        $parts2 = explode(" ", $name2);
        
        // Initialize counters for full and partial matches
        $fullMatches = 0;
        $partialMatches = 0;
        
        // Loop through each part of the first name
        foreach ($parts1 as $part1) {
            // Loop through each part of the second name
            foreach ($parts2 as $part2) {
                // Check if the parts match
                if (strcasecmp($part1, $part2) === 0) {
                    // If they match, increment the appropriate counter
                    if (strlen($part1) == strlen($part2)) {
                        $fullMatches++;
                    } else {
                        $partialMatches++;
                    }
                    // No need to check further for this part
                    break;
                }
            }
        }  
        
        // Determine the overall match result
        if ($fullMatches == count($parts1) && $fullMatches == count($parts2)) {
            return "FULL_MATCH";
        } elseif ($fullMatches > 0 || $partialMatches > 0) {
            return "PARTIAL_MATCH";
        } else {
            return "NO_MATCH";
        }
    }
    
    // public function competitionChart($compe)

}