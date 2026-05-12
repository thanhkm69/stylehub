<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $email,
        public string $password
    ) {}

    public function build(): self
    {
        return $this->subject('Tài khoản StyleHub của bạn')
                    ->view('mails.password');
    }
}