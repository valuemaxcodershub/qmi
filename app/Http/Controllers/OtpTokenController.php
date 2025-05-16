<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\OtpToken;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class OtpTokenController extends Controller
{
    private $otpTimer;
    public function __construct() {
        $this->otpTimer = 10;
    }
    
    public function generateOtpToken($userId, $userType, $useCase = 'login') : string|bool  {
        $userOtp = OtpToken::where(['user_id' => $userId, 'status' => 'new', 'type' => $userType, 'use_case' => $useCase])->first();
        $currentTime = Carbon::now();
        if ($userOtp) {
            $createdAt = Carbon::parse($userOtp->created_at);
            
            if ($createdAt->diffInMinutes($currentTime) > 10) {
                self::updateOtp($userOtp->token, 'expired');
                $otpToken = self::createOtp($userId, $userType, $useCase);
            } else {
                $otpToken = $userOtp->token;
            }

        } else {
           $otpToken = self::createOtp($userId, $userType, $useCase);
        }
        return $otpToken;
    }

    private function createOtp($userId, $userType, $useCase) {
        $otpToken = mt_rand(111, 909).mt_rand(111, 909);
        $createOtp = OtpToken::create([
            'user_id' => $userId,
            'status' => 'new',
            'type' => $userType,
            'token' => $otpToken,
            'use_case' => $useCase
        ]);

        if ($createOtp) {
            return $otpToken;
        }
        return false;
    }
    
    public function verifyOtp($otpToken, $userType = 'user') : array {
        $userOtp = OtpToken::where(['token' => $otpToken, 'status' => 'new', 'type' => $userType])->first();
        $currentTime = Carbon::now();

        if ($userOtp) {
            $createdAt = Carbon::parse($userOtp->created_at);
            if ($createdAt->diffInMinutes($currentTime) > 10) {
                self::updateOtp($otpToken, 'expired');
                return ["status" => false, "message" => "OTP Token ($otpToken) expired"];
            }
            return ["status" => true, "message" => "OTP Token ($otpToken) validated", "data" => $userOtp];
        }
        return ["status" => false, "message" => "OTP Token ($otpToken) does not exists or already used"];
    }
    
    public function updateOtp($otpToken, $status) : bool {
        return OtpToken::where(['token' => $otpToken])->update(['status' => $status]);
    }    
    
    public function deleteOtp($otpToken) : bool {
        return OtpToken::where(['token' => $otpToken])->delete();
    }
    
}
