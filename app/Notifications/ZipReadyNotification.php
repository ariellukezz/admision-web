<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ZipReadyNotification extends Notification
{
    use Queueable;

    protected $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function via($notifiable)
    {
        return [];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Tu archivo ZIP está listo para descargar')
                    ->line('Hemos terminado de preparar tu archivo comprimido.')
                    ->action('Descargar ahora', route('download.prepared', ['filename' => $this->filename]))
                    ->line('Gracias por usar nuestro servicio!');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Tu archivo ZIP está listo para descargar',
            'url' => route('download.prepared', ['filename' => $this->filename])
        ];
    }
}
