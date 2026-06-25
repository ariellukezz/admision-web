<?php

namespace App\Notifications;

// Not queued: crear en BD de forma síncrona
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RevisionCompletadaNotification extends Notification
{
    protected $postulanteNombre;
    protected $postulanteDni;
    protected $documentosVerificados;
    protected $documentosPendientes;
    protected $datosCitacion;
    protected $documentosUrl;

    public function __construct(
        string $postulanteNombre,
        string $postulanteDni,
        array $documentosVerificados = [],
        array $documentosPendientes = [],
        array $datosCitacion = []
    ) {
        $this->postulanteNombre = $postulanteNombre;
        $this->postulanteDni = $postulanteDni;
        $this->documentosVerificados = $documentosVerificados;
        $this->documentosPendientes = $documentosPendientes;
        $this->datosCitacion = $datosCitacion;
        $this->documentosUrl = route('postulante.documentos');
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Revisión de documentos completada')
            ->greeting('Hola, ' . ($notifiable->name ?? 'Postulante'))
            ->line("Tu revisión de documentos ha sido completada.");

        if (!empty($this->documentosVerificados)) {
            $mail->line('**Documentos aprobados:**');
            foreach ($this->documentosVerificados as $doc) {
                $mail->line('✓ ' . $doc);
            }
        }

        if (!empty($this->documentosPendientes)) {
            $mail->line('**Documentos que requieren corrección:**');
            foreach ($this->documentosPendientes as $doc) {
                $mail->line('⚠ ' . $doc);
            }
        }

        if (!empty($this->datosCitacion)) {
            $mail->line('**Cita presencial:**')
                ->line('Fecha: ' . ($this->datosCitacion['fecha'] ?? 'Por confirmar'))
                ->line('Hora: ' . ($this->datosCitacion['hora_inicio'] ?? '') . ' - ' . ($this->datosCitacion['hora_fin'] ?? ''))
                ->line('Lugar: ' . ($this->datosCitacion['lugar'] ?? 'Dirección de Admisión'));

            if (!empty($this->datosCitacion['instrucciones'])) {
                $mail->line($this->datosCitacion['instrucciones']);
            }
        }

        $mail->action('Ver mis documentos', $this->documentosUrl)
            ->line('Gracias por usar el sistema de admisión.');

        return $mail;
    }

    public function toArray($notifiable): array
    {
        return [
            'tipo' => 'revision_completada',
            'mensaje' => 'Tu revisión de documentos ha sido completada',
            'postulante_nombre' => $this->postulanteNombre,
            'postulante_dni' => $this->postulanteDni,
            'documentos_verificados' => $this->documentosVerificados,
            'documentos_pendientes' => $this->documentosPendientes,
            'fecha_cita' => $this->datosCitacion['fecha'] ?? null,
            'hora_inicio' => $this->datosCitacion['hora_inicio'] ?? null,
            'hora_fin' => $this->datosCitacion['hora_fin'] ?? null,
            'lugar' => $this->datosCitacion['lugar'] ?? null,
            'instrucciones' => $this->datosCitacion['instrucciones'] ?? null,
        ];
    }
}
