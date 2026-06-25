<?php

namespace App\Notifications;

// Not queued: crear en BD de forma síncrona
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SolicitudRevisionNotification extends Notification
{

    protected $postulanteNombre;
    protected $postulanteDni;
    protected $veces;
    protected $documentosUrl;

    public function __construct(string $postulanteNombre, string $postulanteDni, int $veces = 1)
    {
        $this->postulanteNombre = $postulanteNombre;
        $this->postulanteDni = $postulanteDni;
        $this->veces = $veces;
        $this->documentosUrl = route('postulante.documentos');
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $asunto = $this->veces > 1
            ? 'Recordatorio de solicitud de revisión de documentos'
            : 'Solicitud de revisión de documentos recibida';

        return (new MailMessage)
            ->subject($asunto)
            ->greeting('Hola, ' . ($notifiable->name ?? 'Postulante'))
            ->line("Tu solicitud de revisión de documentos ha sido registrada exitosamente.")
            ->line("Nombre: {$this->postulanteNombre}")
            ->line("DNI: {$this->postulanteDni}")
            ->line("Un revisor verificará tus documentos y te notificará el resultado.")
            ->action('Ver mis documentos', $this->documentosUrl)
            ->line('Gracias por usar el sistema de admisión.');
    }

    public function toArray($notifiable): array
    {
        return [
            'tipo' => 'solicitud_revision',
            'mensaje' => 'Tu solicitud de revisión de documentos ha sido registrada',
            'postulante_nombre' => $this->postulanteNombre,
            'postulante_dni' => $this->postulanteDni,
            'veces' => $this->veces,
        ];
    }
}
