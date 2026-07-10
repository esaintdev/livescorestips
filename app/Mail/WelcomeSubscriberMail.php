<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeSubscriberMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subscriberEmail;

    /**
     * Create a new message instance.
     */
    public function __construct($email)
    {
        $this->subscriberEmail = $email;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Welcome to bet365! 🎯⚽')
            ->view('emails.welcome-subscriber');
    }
}
