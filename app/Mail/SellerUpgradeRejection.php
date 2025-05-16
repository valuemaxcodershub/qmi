<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SellerUpgradeRejection extends Mailable
{
    use Queueable, SerializesModels;

    public $user, $rejectionData;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $rejectionData)
    {
        $this->user = $user;
        $this->rejectionData = $rejectionData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user = $this->user;
        $rejectionData = $this->rejectionData;
        $messageContent = $rejectionData['message'];
        return $this->subject('PAVI - Business Upgrade Declined!')->view('email-templates.upgrade.declined', compact('user', 'messageContent'));
    }
}
