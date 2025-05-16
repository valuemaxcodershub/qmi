<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

class SellerUpgradeRequest extends Mailable
{
    use Queueable, SerializesModels;
    public $seller , $newSellerTypeInfo;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($seller, $newSellerTypeInfo)
    {
        $this->seller = $seller;
        $this->newSellerTypeInfo = $newSellerTypeInfo;
    }

    // public function envelope()
    // {
    //     return new Envelope(
    //         from: new Address('no-reply@peaktopup.com', 'Reset Password Request'),
    //         subject: 'Reset Your Password',
    //     );
    // }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Seller Upgrade Request')->view('email-templates.upgrade-request-submission');
    }
}
