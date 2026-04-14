<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PublicarResultadosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;
    public $tries = 3;
    public $timeout = 120;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        DB::beginTransaction();

        try {

            $vacante = DB::table('vacantes')
                ->where('id_proceso', $this->data['id_proceso'])
                ->where('id_programa', $this->data['id_programa'])
                ->lockForUpdate()
                ->first();

            if ($vacante && $vacante->publicado == 1) {
                DB::rollBack();
                return;
            }

            foreach ($this->data['lista'] as $item) {

                // 🔍 DEBUG (puedes borrar luego)
                Log::info('ITEM JOB', $item);

                try {

                    // (opcional) validar que exista
                    $existe = DB::table('resultados_segundas')
                        ->where('id_pre_inscripcion', $item['id_pre_inscripcion'])
                        ->exists();

                    if (!$existe) {
                        Log::warning('NO EXISTE REGISTRO', $item);
                        continue;
                    }

                    // ✅ UPDATE DIRECTO (SIN COMPLICACIONES)
                    DB::table('resultados_segundas')
                        ->where('id_pre_inscripcion', $item['id_pre_inscripcion'])
                        ->update([
                            'puntaje' => $item['puntaje'] ?? null,
                            'puesto' => $item['puesto'] ?? null,
                            'apto' => strtoupper($item['apto']) ?? null,
                            'updated_at' => now(),
                        ]);

                } catch (Exception $eItem) {

                    DB::table('errores_proceso')->insert([
                        'id_pre_inscripcion' => $item['id_pre_inscripcion'],
                        'puesto' => $item['puesto'] ?? null,
                        'puntaje' => $item['puntaje'] ?? null,
                        'error' => $eItem->getMessage(),
                        'data' => json_encode($item),
                        'created_at' => now()
                    ]);

                    Log::error('ERROR ITEM', [
                        'item' => $item,
                        'error' => $eItem->getMessage(),
                        'line' => $eItem->getLine(),
                        'file' => $eItem->getFile()
                    ]);
                }
            }

            DB::table('vacantes')
                ->where('id_proceso', $this->data['id_proceso'])
                ->where('id_programa', $this->data['id_programa'])
                ->update([
                    'vacantes' => $this->data['vacantes'],
                    'asignados' => collect($this->data['lista'])->where('apto', 'SI')->count(),
                    'publicado' => 1,
                    'updated_at' => now()
                ]);

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            Log::error('ERROR GENERAL JOB', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $this->data
            ]);

            throw $e;
        }
    }

    public function failed(Exception $e)
    {
        Log::error('JOB FALLIDO DEFINITIVO', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'data' => $this->data
        ]);
    }
}