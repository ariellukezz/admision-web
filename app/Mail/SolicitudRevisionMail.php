<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SolicitudRevisionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $postulanteNombre;
    public $postulanteDni;
    public $veces;

    public function __construct(string $postulanteNombre, string $postulanteDni, int $veces = 1)
    {
        $this->postulanteNombre = $postulanteNombre;
        $this->postulanteDni = $postulanteDni;
        $this->veces = $veces;
    }

    public function build(): static
    {
        $asunto = $this->veces > 1
            ? 'Recordatorio: Solicitud de revisión de documentos'
            : 'Solicitud de revisión de documentos recibida';

        return $this->subject($asunto)
            ->view('emails.solicitud-revision')
            ->with([
                'postulanteNombre' => $this->postulanteNombre,
                'postulanteDni' => $this->postulanteDni,
                'veces' => $this->veces,
            ]);
    }
}
