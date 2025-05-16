<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerUpgradeInReview extends Mailable
{
    use Queueable, SerializesModels;
    public $user, $upgradeData;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $upgradeData)
    {
        $this->user = $user;
        $this->upgradeData = $upgradeData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user = $this->user;
        $upgradeData = $this->upgradeData;
        return $this->subject('Seller Upgrade Notification')->view('email-templates.upgrade.review', compact('upgradeData', 'user'));
    }
}
