<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\MensajeCorreo;

class EnviarCorreoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $destinatario;
    public $mailers;
    public $tries = 2;
    public $timeout = 60;

    public function __construct($destinatario, array $mailers)
    {
        $this->destinatario = $destinatario;
        $this->mailers = $mailers;
        $this->onQueue('default');
    }

    public function handle()
    {
        $mailer = $this->mailers[array_rand($this->mailers)];

        try {
            Mail::mailer($mailer)
                ->to($this->destinatario->correo)
                ->send(new MensajeCorreo(
                    $this->destinatario->nombres,
                    $this->destinatario->correo,
                    $this->destinatario->programa,
                    $this->destinatario->puerta
                ));

            DB::table('enviar_correos')
                ->where('id', $this->destinatario->id)
                ->update(['enviado' => 1]);

        } catch (Exception $e) {
            $otrosSMTP = array_diff($this->mailers, [$mailer]);
            $nuevoMailer = $otrosSMTP[array_rand($otrosSMTP)];

            try {
                Mail::mailer($nuevoMailer)
                    ->to($this->destinatario->correo)
                    ->send(new MensajeCorreo(
                        $this->destinatario->nombres,
                        $this->destinatario->correo,
                        $this->destinatario->programa,
                        $this->destinatario->puerta
                    ));

                DB::table('enviar_correos')
                    ->where('id', $this->destinatario->id)
                    ->update(['enviado' => 1]);

            } catch (Exception $e2) {
                throw $e2;
            }
        }
    }
}
