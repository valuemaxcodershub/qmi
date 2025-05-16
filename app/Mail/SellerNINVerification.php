<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerNINVerification extends Mailable
{
    use Queueable, SerializesModels;
    public $user, $action;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $action = 'approve')
    {
        $this->user = $user;
        $this->action = $action;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user = $this->user;
        $action = $this->action;
        if ($action == 'approve') {
            return $this->subject('KYC Verification Successful - PAVI NG')->view('email-templates.verification.nin-approved', compact('user'));
        } else {
            return $this->subject('KYC Verification Failed - PAVI NG')->view('email-templates.verification.nin-declined', compact('user'));
        }
    }
}
