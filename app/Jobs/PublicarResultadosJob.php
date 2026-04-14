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

            Log::info('ITEM JOB', $item);

            $nuevoCodigo = null;

            try {

                // 🔎 Obtener datos del postulante
                $re = DB::table('pre_inscripcion as pre')
                    ->select(
                        'pro.programa_oti',
                        'pro.cod_esp',
                        'pos.primer_apellido as paterno',
                        'pos.segundo_apellido as materno',
                        'pos.nombres',
                        'td.documento_oti as tipo_doc_oti',
                        'pos.nro_doc as dni',
                        'pos.fec_nacimiento',
                        'pos.sexo',
                        'pos.ubigeo_residencia',
                        'mo.modalidad_oti',
                        'pos.estado_civil',
                        'pos.direccion',
                        'pos.email',
                        'pos.celular',
                        'p.anio',
                        'p.ciclo_oti'
                    )
                    ->join('programa as pro', 'pro.id', '=', 'pre.id_programa')
                    ->join('postulante as pos', 'pos.id', '=', 'pre.id_postulante')
                    ->join('modalidad as mo', 'mo.id', '=', 'pre.id_modalidad')
                    ->join('procesos as p', 'p.id', '=', 'pre.id_proceso')
                    ->join('tipo_documento_identidad as td', 'td.id', '=', 'pos.tipo_doc')
                    ->where('pre.id', $item['id_pre_inscripcion'])
                    ->where('pre.id_proceso', $this->data['id_proceso'])
                    ->where('pre.estado', 1)
                    ->first();

                // 🎯 SOLO SI ES APTO
                if ($re && strtoupper($item['apto']) === 'SI') {

                    // 🔍 Buscar si ya existe en BD secundaria
                    $estudiante = DB::connection($database2)
                        ->table('unapnet.estudiante')
                        ->where('num_doc', $re->dni)
                        ->where('cod_car', $re->programa_oti)
                        ->where('ano_ing', $re->anio)
                        ->first();

                    if ($estudiante) {

                        // ✅ usar código existente
                        $nuevoCodigo = $estudiante->num_mat;

                        Log::info('CODIGO EXISTENTE', [
                            'dni' => $re->dni,
                            'codigo' => $nuevoCodigo
                        ]);

                    } else {

                        // 🔢 generar nuevo código
                        $max = DB::connection($database2)
                            ->table('unapnet.estudiante as e')
                            ->whereRaw("LEFT(e.num_mat, 2) = ?", [$prefijo])
                            ->max(DB::raw("CAST(SUBSTRING(e.num_mat, 3) AS UNSIGNED)"));

                        $nuevoNumero = ($max ?? 0) + 1;
                        $nuevoCodigo = $prefijo . str_pad($nuevoNumero, 4, '0', STR_PAD_LEFT);

                        Log::info('GENERANDO CODIGO', [
                            'dni' => $re->dni,
                            'codigo' => $nuevoCodigo
                        ]);

                        // 💾 insertar en 2da BD
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
                                'con_est' => 5,
                                'celular' => $re->celular,
                                'cod_esp' => $re->cod_esp,
                                'puntaje' => $item['puntaje'],
                                'puesto_escuela' => $item['puesto'],
                                'puesto_general' => $item['puesto'],
                                'ano_ing' => $re->anio,
                                'per_ing' => $re->ciclo_oti
                            ]);
                    }
                }

            } catch (Exception $eItem) {

                Log::error('ERROR ITEM', [
                    'item' => $item,
                    'error' => $eItem->getMessage()
                ]);
            }

            // ✅ UPDATE FINAL (NO PISA CON NULL)
            $update = [
                'puntaje' => $item['puntaje'] ?? null,
                'puesto' => $item['puesto'] ?? null,
                'apto' => strtoupper($item['apto']) ?? null,
                'updated_at' => now(),
            ];

            if ($nuevoCodigo !== null) {
                $update['codigo_ingreso'] = $nuevoCodigo;
            }

            DB::table('resultados_segundas')
                ->where('id_pre_inscripcion', $item['id_pre_inscripcion'])
                ->update($update);
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