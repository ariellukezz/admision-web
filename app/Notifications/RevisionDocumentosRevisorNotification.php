<?php

namespace App\Notifications;

// Not queued: crear en BD de forma síncrona
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RevisionDocumentosRevisorNotification extends Notification
{

    protected $postulanteNombre;
    protected $postulanteDni;
    protected $veces;
    protected $perfilUrl;

    public function __construct(string $postulanteNombre, string $postulanteDni, int $veces = 1)
    {
        $this->postulanteNombre = $postulanteNombre;
        $this->postulanteDni = $postulanteDni;
        $this->veces = $veces;
        $this->perfilUrl = route('revisor.postulante-perfil', $postulanteDni);
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $vecesTexto = $this->veces > 1 ? " (solicitud #{$this->veces})" : '';

        return (new MailMessage)
            ->subject('Solicitud de revisión de documentos' . $vecesTexto)
            ->line("El postulante {$this->postulanteNombre} (DNI: {$this->postulanteDni}) ha solicitado la revisión de sus documentos{$vecesTexto}.")
            ->action('Ver postulante', $this->perfilUrl)
            ->line('Por favor, revisa los documentos lo antes posible.');
    }

    public function toArray($notifiable): array
    {
        $vecesTexto = $this->veces > 1 ? " (insistencia #{$this->veces})" : '';

        return [
            'tipo' => 'revision_documentos',
            'mensaje' => "El postulante {$this->postulanteNombre} solicita revisión de sus documentos{$vecesTexto}",
            'postulante_nombre' => $this->postulanteNombre,
            'postulante_dni' => $this->postulanteDni,
            'veces' => $this->veces,
            'url' => $this->perfilUrl,
        ];
    }
}
