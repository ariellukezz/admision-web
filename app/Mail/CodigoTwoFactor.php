<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CodigoTwoFactor extends Mailable
{
    use Queueable, SerializesModels;

    public $codigo;
    public $nombre;

    public function __construct(string $codigo, string $nombre = '')
    {
        $this->codigo = $codigo;
        $this->nombre = $nombre;
    }

    public function build(): static
    {
        return $this->subject('Código de verificación - Sistema de Admisión')
            ->view('emails.codigo-2fa')
            ->with([
                'codigo' => $this->codigo,
                'nombre' => $this->nombre,
                'fecha' => now()->format('d/m/Y H:i'),
            ]);
    }
}
