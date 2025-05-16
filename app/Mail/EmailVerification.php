<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $token;
    private string $clientName;

    public function __construct($user, $token)
    {
        $this->clientName = $user->f_name . ' '.$user->l_name;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(translate('Email_Verification'))->view('email-templates.email-verification', ['token' => $this->token, 'clientName' => $this->clientName]);
    }
}
