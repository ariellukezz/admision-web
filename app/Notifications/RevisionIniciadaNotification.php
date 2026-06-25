<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RevisionIniciadaNotification extends Notification
{
    protected $postulanteNombre;
    protected $postulanteDni;
    protected $revisorNombre;
    protected $documentosUrl;

    public function __construct(string $postulanteNombre, string $postulanteDni, string $revisorNombre)
    {
        $this->postulanteNombre = $postulanteNombre;
        $this->postulanteDni = $postulanteDni;
        $this->revisorNombre = $revisorNombre;
        $this->documentosUrl = route('postulante.documentos');
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu revisión de documentos ha iniciado')
            ->greeting('Hola, ' . ($notifiable->name ?? 'Postulante'))
            ->line("El revisor {$this->revisorNombre} ha iniciado la revisión de tus documentos.")
            ->line('Te notificaremos cuando el proceso haya concluido.')
            ->action('Ver mis documentos', $this->documentosUrl)
            ->line('Gracias por usar el sistema de admisión.');
    }

    public function toArray($notifiable): array
    {
        return [
            'tipo' => 'revision_iniciada',
            'mensaje' => 'Un revisor ha iniciado la revisión de tus documentos',
            'postulante_nombre' => $this->postulanteNombre,
            'postulante_dni' => $this->postulanteDni,
            'revisor_nombre' => $this->revisorNombre,
        ];
    }
}
