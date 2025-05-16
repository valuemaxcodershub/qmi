<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    protected $reset_url;
    public $customerName;

    public function __construct($reset_url, $customerName)
    {
        $this->reset_url = $reset_url;
        $this->customerName = $customerName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $reset_url = $this->reset_url;
        return $this->subject(translate('Password_Reset_Mail'))->view('email-templates.admin-password-reset', ['url' => $reset_url]);
    }
}
