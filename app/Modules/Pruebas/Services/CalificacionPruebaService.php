<?php

namespace App\Modules\Pruebas\Services;

use App\Modules\Pruebas\Models\Prueba;
use App\Modules\Pruebas\Models\PruebaRes;
use App\Modules\Pruebas\Models\PruebaExcepcion;
use App\Modules\Pruebas\Exports\PruebaPostulantesExport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CalificacionPruebaService
{
    public function calificar(int $idPrueba, int $idPonderacion, int $idMultiplicador): array
    {
        $prueba = Prueba::find($idPrueba);
        if (!$prueba) {
            throw new \DomainException('Prueba no encontrada');
        }

        // Guardar la ponderación y multiplicador usados en la prueba
        $prueba->update([
            'id_ponderacion' => $idPonderacion,
            'id_multiplicador' => $idMultiplicador,
        ]);

        $multiplicador = DB::table('multiplicadores')->find($idMultiplicador);
        if (!$multiplicador) {
            throw new \DomainException('Multiplicador no encontrado');
        }

        $valCorrecta = (float) $multiplicador->correcta;
        $valIncorrecta = (float) $multiplicador->incorrecta;
        $valBlanco = (float) $multiplicador->blanco;

        $pondMap = $this->buildPondMap($idPonderacion);

        // Reset: limpiar calificación previa para permitir recalificar
        PruebaRes::where('id_prueba', $idPrueba)->update([
            'puntaje' => null,
            'calificado' => false,
        ]);

        $tipos = DB::table('prueba_tipos')
            ->where('id_prueba', $idPrueba)
            ->whereNotNull('respuestas')
            ->get()
            ->keyBy('tipo');

        if ($tipos->isEmpty()) {
            throw new \DomainException('No hay tipos cargados para esta prueba');
        }

        $respuestas = DB::select(
            "SELECT r.id, r.litho, r.respuestas, i.tipo, i.dni, i.aula
            FROM prueba_res r
            JOIN prueba_ides i ON i.litho = r.litho AND i.id_prueba = r.id_prueba
            WHERE r.id_prueba = ? AND i.estado = 1
            ORDER BY r.id",
            [$idPrueba]
        );

        if (empty($respuestas)) {
            throw new \DomainException('No hay respuestas vinculadas a ides para calificar');
        }

        $excCache = [];
        $calificados = 0;

        foreach ($respuestas as $line) {
            if (!$line->tipo) continue;

            $tipoRow = $tipos->get($line->tipo);
            if (!$tipoRow) continue;

            $clave = $tipoRow->respuestas;
            $excepciones = $this->getExcepciones($excCache, $tipoRow->id);

            $puntaje = $this->calcularPuntaje(
                $line->respuestas,
                $clave,
                $pondMap,
                $excepciones,
                $valCorrecta,
                $valIncorrecta,
                $valBlanco
            );

            PruebaRes::where('id', $line->id)->update([
                'puntaje' => round($puntaje, 3),
                'calificado' => true,
            ]);

            $calificados++;
        }

        return [
            'calificados' => $calificados,
            'total_respuestas' => count($respuestas),
        ];
    }

    public function getResultados(int $idPrueba, bool $conPostulante = false): array
    {
        $prueba = Prueba::find($idPrueba);
        if (!$prueba) {
            throw new \DomainException('Prueba no encontrada');
        }

        $sql = $conPostulante
            ? "SELECT r.id, r.litho, r.respuestas, r.puntaje, r.calificado,
                      i.tipo, i.dni, i.aula,
                      p.primer_apellido, p.segundo_apellido, p.nombres
               FROM prueba_res r
               JOIN prueba_ides i ON i.litho = r.litho AND i.id_prueba = r.id_prueba
               LEFT JOIN postulante p ON p.nro_doc = i.dni
               WHERE r.id_prueba = ?
               ORDER BY r.puntaje DESC"
            : "SELECT r.id, r.litho, r.respuestas, r.puntaje, r.calificado,
                      i.tipo, i.dni, i.aula
               FROM prueba_res r
               JOIN prueba_ides i ON i.litho = r.litho AND i.id_prueba = r.id_prueba
               WHERE r.id_prueba = ?
               ORDER BY r.puntaje DESC";

        $data = DB::select($sql, [$idPrueba]);

        return [
            'total' => count($data),
            'calificados' => count(array_filter($data, fn($d) => $d->calificado)),
            'data' => $data,
        ];
    }

    public function exportExcel(int $idPrueba, bool $conPostulante = false): BinaryFileResponse
    {
        $resultados = $this->getResultados($idPrueba, $conPostulante);

        $collection = collect($resultados['data'])->map(function ($r) use ($conPostulante) {
            return [
                'dni' => $r->dni ?? '',
                'paterno' => $conPostulante ? ($r->primer_apellido ?? '') : '',
                'materno' => $conPostulante ? ($r->segundo_apellido ?? '') : '',
                'nombres' => $conPostulante ? ($r->nombres ?? '') : '',
                'litho' => $r->litho ?? '',
                'tipo' => $r->tipo ?? '',
                'aula' => $r->aula ?? '',
                'respuestas' => $r->respuestas ?? '',
                'puntaje' => $r->puntaje ?? 0,
                'calificado' => $r->calificado ? 'SI' : 'NO',
            ];
        });

        return Excel::download(
            new PruebaPostulantesExport($collection),
            'prueba_' . $idPrueba . '_resultados.xlsx'
        );
    }

    private function buildPondMap(int $idPonderacion): array
    {
        $ponderaciones = DB::select(
            "SELECT numero, ponderacion, cantidad_preguntas
             FROM ponderacion
             WHERE id_ponderacion_simulacro = ?
             ORDER BY numero",
            [$idPonderacion]
        );

        $pondMap = [];
        $qIndex = 1;

        foreach ($ponderaciones as $p) {
            $cantidad = (int) ($p->cantidad_preguntas ?? 1);
            for ($j = 0; $j < $cantidad; $j++) {
                $pondMap[$qIndex] = (float) $p->ponderacion;
                $qIndex++;
            }
        }

        return $pondMap;
    }

    private function getExcepciones(array &$cache, int $idTipo): mixed
    {
        if (!isset($cache[$idTipo])) {
            $cache[$idTipo] = PruebaExcepcion::where('id_prueba_tipo', $idTipo)
                ->get()
                ->keyBy('nro_pregunta');
        }
        return $cache[$idTipo];
    }

    private function calcularPuntaje(
        string $respuestas,
        string $clave,
        array $pondMap,
        mixed $excepciones,
        float $valCorrecta,
        float $valIncorrecta,
        float $valBlanco
    ): float {
        $puntaje = 0;
        $nPreguntas = 60;

        for ($i = 0; $i < $nPreguntas; $i++) {
            $resp = $respuestas[$i] ?? ' ';
            $pat = $clave[$i] ?? ' ';
            $pond = $pondMap[$i + 1] ?? 0;
            $excepcion = $excepciones->get($i + 1);

            if ($excepcion) {
                $puntaje += match ($excepcion->accion) {
                    'todas_validas' => (float) $excepcion->puntaje,
                    'multiples_validas' => in_array($resp, array_map('trim', explode(',', $excepcion->claves_validas ?? '')))
                        ? $pond * $valCorrecta
                        : $pond * $valIncorrecta,
                    'anulada' => 0.0,
                    'asignar_puntaje' => (float) $excepcion->puntaje,
                    default => 0.0,
                };
            } else {
                if ($resp === ' ') {
                    $puntaje += $pond * $valBlanco;
                } elseif ($resp === $pat) {
                    $puntaje += $pond * $valCorrecta;
                } else {
                    $puntaje += $pond * $valIncorrecta;
                }
            }
        }

        return $puntaje;
    }
}
