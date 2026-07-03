<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestSmtp extends Mailable
{
    use Queueable, SerializesModels;

    public $accountName;

    public function __construct(string $accountName = '')
    {
        $this->accountName = $accountName;
    }

    public function build(): static
    {
        return $this->subject('Prueba de correo SMTP - Admisión UNAP')
            ->view('emails.test-smtp')
            ->with([
                'accountName' => $this->accountName,
            ]);
    }
}
