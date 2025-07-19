<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MensajeCorreo extends Mailable
{
    use Queueable, SerializesModels;

    public $nombre;
    public $correo;
    public $programa;
    public $puerta;


    public function __construct($nombre, $correo, $programa, $puerta)
    {
        $this->nombre = $nombre;
        $this->correo = $correo;
        $this->programa = $programa;
        $this->puerta = $puerta;
        
    }

    public function build()
    {
        return $this->view('emails.notificaciones.notificacion_puerta', [
            'nombre' => $this->nombre,
            'correo' => $this->correo,
            'programa' => $this->programa,
            'puerta' => $this->puerta,
        ])
        ->subject('Notificación puerta de ingreso');
    }


}
