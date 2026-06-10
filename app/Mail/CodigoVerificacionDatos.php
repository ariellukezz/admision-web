<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CodigoVerificacionDatos extends Mailable
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
        return $this->subject('Código de verificación - Cargar datos')
            ->view('emails.codigo-verificacion-datos')
            ->with([
                'codigo' => $this->codigo,
                'nombre' => $this->nombre,
            ]);
    }
}
