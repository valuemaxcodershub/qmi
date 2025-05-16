<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerRegVerify extends Mailable
{
    use Queueable, SerializesModels;
    public $sellerName, $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($sellerName, $token)
    {
        $this->sellerName = $sellerName;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $sellerName = $this->sellerName;
        $token = $this->token;
        return $this->subject('Welcome - Email Verfication')->view('email-templates.seller-reg-verify', compact('token', 'sellerName'));
    }
}
