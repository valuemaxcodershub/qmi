<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoginOTP extends Mailable
{
    use Queueable, SerializesModels;
    public $clientName, $otpCode, $userType;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($clientName, $userType, $otpCode)
    {
        $this->clientName = $clientName;
        $this->otpCode = $otpCode;
        $this->userType = $userType;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $clientName = $this->clientName;
        $otpCode = $this->otpCode;
        $userType = $this->userType;
        return $this->subject("Here's the 6-digit verification code you requested")->view('email-templates.login-otp', compact('otpCode', 'clientName', 'userType'));
    }
}
