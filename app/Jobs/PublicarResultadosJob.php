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

            $database2 = 'mysql_secondary';
            $prefijo = '26';

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

                try {

                    $re = DB::table('pre_inscripcion as pre')
                        ->select(
                            'pro.programa_oti',
                            'pro.cod_esp',
                            'pos.primer_apellido as paterno',
                            'pos.segundo_apellido as materno',
                            'pos.nombres',
                            'pos.tipo_doc as tipo_doc_oti',
                            'pos.nro_doc as dni',
                            'pos.fec_nacimiento',
                            'pos.sexo',
                            'pos.ubigeo_residencia',
                            'mo.modalidad_oti',
                            'pos.estado_civil',
                            'pos.direccion',
                            'pos.email',
                            'pos.celular',
                            're.puntaje',
                            're.puesto',
                            're.puesto_general',
                            'p.anio',
                            'p.ciclo_oti'
                        )
                        ->join('resultados_segundas as re', 're.id_pre_inscripcion', '=', 'pre.id')
                        ->join('programa as pro', 'pro.id', '=', 'pre.id_programa')
                        ->join('postulante as pos', 'pos.id', '=', 'pre.id_postulante')
                        ->join('modalidad as mo', 'mo.id', '=', 'pre.id_modalidad')
                        ->join('procesos as p', 'p.id', '=', 'pre.id_proceso')
                        ->where('pre.id', $item['id_pre_inscripcion'])
                        ->where('pre.id_proceso', $this->data['id_proceso'])
                        ->where('pre.estado', 1)
                        ->first();

                    if (!$re) continue;

                    $nuevoCodigo = null;

                    if ($item['apto'] === 'SI') {

                        $registrado = DB::connection('third')
                        ->table('estudiante')
                        ->where('num_doc', $re->dni)
                        ->where('cod_car', $re->programa_oti)
                        ->where('ano_ing', $re->anio)
                        ->first();

                        if ($registrado) {

                            $nuevoCodigo = $registrado->num_mat;

                        } else {

                            $max = DB::connection($database2)
                                ->table('unapnet.estudiante as e')
                                ->whereRaw("LEFT(e.num_mat, 2) = ?", [$prefijo])
                                ->max(DB::raw("CAST(SUBSTRING(e.num_mat, 3) AS UNSIGNED)"));

                            $nuevoNumero = ($max ?? 0) + 1;
                            $nuevoCodigo = $prefijo . str_pad($nuevoNumero, 4, '0', STR_PAD_LEFT);

                            DB::connection($database2)
                                ->table('unapnet.estudiante')
                                ->insert([
                                    'num_mat' => $nuevoCodigo,
                                    'cod_car' => $re->programa_oti,
                                    'paterno' => mb_strtoupper($re->paterno, 'UTF-8'),
                                    'materno' => mb_strtoupper($re->materno, 'UTF-8'),
                                    'nombres' => mb_strtoupper($re->nombres, 'UTF-8'),
                                    'tip_doc' => $re->tipo_doc_oti,
                                    'num_doc' => $re->dni,
                                    'num_car' => 1,
                                    'fch_nac' => $re->fec_nacimiento,
                                    'sexo' => $re->sexo,
                                    'ubigeo' => $re->ubigeo_residencia,
                                    'mod_ing' => $re->modalidad_oti,
                                    'est_civ' => [1 => 2, 2 => 1, 3 => 3, 4 => 6][$re->estado_civil] ?? 1,
                                    'fch_ing' => now(),
                                    'direc' => $re->direccion,
                                    'email' => $re->email,
                                    'emailins' => null,
                                    'con_est' => 5,
                                    'celular' => $re->celular,
                                    'cod_esp' => $re->cod_esp,
                                    'puntaje' => $re->puntaje,
                                    'puesto_escuela' => $re->puesto,
                                    'puesto_general' => $re->puesto_general,
                                    'ano_ing' => $re->anio,
                                    'per_ing' => $re->ciclo_oti
                                ]);
                        }
                    }

                    DB::table('resultados_segundas')->updateOrInsert(
                        ['id_pre_inscripcion' => $item['id_pre_inscripcion']],
                        [
                            'puntaje' => $item['puntaje'],
                            'puesto' => $item['puesto'],
                            'apto' => $item['apto'],
                            'codigo_ingreso' => $nuevoCodigo,
                            'updated_at' => now(),
                        ]
                    );

                } catch (Exception $eItem) {

                    Log::error('ERROR ITEM', [
                        'item' => $item,
                        'error' => $eItem->getMessage(),
                        'line' => $eItem->getLine(),
                        'file' => $eItem->getFile()
                    ]);

                    continue;
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