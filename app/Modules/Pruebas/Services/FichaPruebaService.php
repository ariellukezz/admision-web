<?php

namespace App\Modules\Pruebas\Services;

use App\Modules\Pruebas\Models\Prueba;
use App\Modules\Pruebas\Models\PruebaIde;
use App\Modules\Pruebas\Models\PruebaRes;
use App\Modules\Pruebas\Models\PruebaExcepcion;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Mpdf\Mpdf;

class FichaPruebaService
{
    public function rankingPdf(int $idPrueba, bool $conPostulante = false): Response
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
               ORDER BY r.puntaje DESC, i.dni ASC"
            : "SELECT r.id, r.litho, r.respuestas, r.puntaje, r.calificado,
                      i.tipo, i.dni, i.aula
               FROM prueba_res r
               JOIN prueba_ides i ON i.litho = r.litho AND i.id_prueba = r.id_prueba
               WHERE r.id_prueba = ?
               ORDER BY r.puntaje DESC, i.dni ASC";

        $data = DB::select($sql, [$idPrueba]);

        $total = count($data);
        $calificados = count(array_filter($data, fn($d) => $d->calificado));
        $noCalificados = $total - $calificados;

        $puntajes = array_filter(array_map(fn($d) => (float) $d->puntaje, $data), fn($p) => $p > 0);
        $promedio = count($puntajes) > 0 ? round(array_sum($puntajes) / count($puntajes), 3) : 0;
        $puntajeMaximo = count($puntajes) > 0 ? max($puntajes) : 0;
        $puntajeMinimo = count($puntajes) > 0 ? min($puntajes) : 0;

        $html = View::make('pruebas::ranking', [
            'prueba' => $prueba,
            'data' => $data,
            'total' => $total,
            'calificados' => $calificados,
            'noCalificados' => $noCalificados,
            'promedio' => number_format($promedio, 3),
            'puntajeMaximo' => number_format($puntajeMaximo, 3),
            'puntajeMinimo' => number_format($puntajeMinimo, 3),
        ])->render();

        $mpdf = $this->createMpdf();
        $mpdf->SetTitle("Ranking — {$prueba->nombre}");
        $mpdf->SetAuthor('Sistema de Admisión UNAP');
        $mpdf->WriteHTML($html);

        $filename = "ranking_prueba_{$idPrueba}_" . date('YmdHis') . '.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function fichaPdf(int $idPrueba, string $dni): Response
    {
        $prueba = Prueba::find($idPrueba);
        if (!$prueba) {
            throw new \DomainException('Prueba no encontrada');
        }

        $html = $this->buildFichaHtml($prueba, $dni);

        $mpdf = $this->createMpdf();
        $mpdf->SetTitle("Ficha — {$dni}");
        $mpdf->SetAuthor('Sistema de Admisión UNAP');
        $mpdf->WriteHTML($html);

        $filename = "ficha_{$dni}_" . date('YmdHis') . '.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function fichaPdfMasivo(int $idPrueba, array $dnis): Response
    {
        $prueba = Prueba::find($idPrueba);
        if (!$prueba) {
            throw new \DomainException('Prueba no encontrada');
        }

        $mpdf = $this->createMpdf();
        $mpdf->SetTitle('Fichas de Prueba — Lote');
        $mpdf->SetAuthor('Sistema de Admisión UNAP');

        $procesados = 0;
        foreach ($dnis as $dni) {
            $html = $this->buildFichaHtml($prueba, $dni);
            if ($procesados > 0) {
                $mpdf->AddPage();
            }
            $mpdf->WriteHTML($html);
            $procesados++;
        }

        if ($procesados === 0) {
            throw new \DomainException('No se encontraron postulantes con IDE');
        }

        $filename = "fichas_prueba_{$idPrueba}_" . date('YmdHis') . '.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    private function buildFichaHtml(Prueba $prueba, string $dni): string
    {
        $ide = DB::table('prueba_ides')
            ->where('id_prueba', $prueba->id)
            ->where('dni', $dni)
            ->where('estado', 1)
            ->first();

        if (!$ide) {
            return '<p>No se encontró IDE para el DNI: ' . e($dni) . '</p>';
        }

        $res = DB::table('prueba_res')
            ->where('id_prueba', $prueba->id)
            ->where('litho', $ide->litho)
            ->first();

        $tipo = DB::table('prueba_tipos')
            ->where('id_prueba', $prueba->id)
            ->where('tipo', $ide->tipo)
            ->first();

        $multiplicador = $prueba->id_multiplicador
            ? DB::table('multiplicadores')->find($prueba->id_multiplicador)
            : null;

        $valCorrecta = (float) ($multiplicador?->correcta ?? 1);
        $valIncorrecta = (float) ($multiplicador?->incorrecta ?? 0);
        $valBlanco = (float) ($multiplicador?->blanco ?? 0);

        $pondMap = $prueba->id_ponderacion
            ? $this->buildPondMap($prueba->id_ponderacion)
            : [];

        $excepciones = $tipo
            ? PruebaExcepcion::where('id_prueba_tipo', $tipo->id)->get()->keyBy('nro_pregunta')
            : collect();

        $respuestas = $res?->respuestas ?? str_repeat(' ', 60);
        $clave = $tipo?->respuestas ?? str_repeat(' ', 60);
        $calificado = $res?->calificado ?? false;
        $puntajeAlmacenado = $res?->puntaje ?? null;
        $tieneRes = $res !== null;

        $detalle = [];
        $puntajeTotal = 0;
        $correctas = 0;
        $incorrectas = 0;
        $blancos = 0;

        for ($i = 0; $i < 60; $i++) {
            $resp = $respuestas[$i] ?? ' ';
            $pat = $clave[$i] ?? ' ';
            $pond = $pondMap[$i + 1] ?? 0;
            $excepcion = $excepciones->get($i + 1);

            if ($excepcion) {
                $estado = 'EXC: ' . $excepcion->accion;
                $puntos = match ($excepcion->accion) {
                    'todas_validas' => (float) $excepcion->puntaje,
                    'multiples_validas' => in_array($resp, array_map('trim', explode(',', $excepcion->claves_validas ?? '')))
                        ? $pond * $valCorrecta
                        : $pond * $valIncorrecta,
                    'anulada' => 0.0,
                    'asignar_puntaje' => (float) $excepcion->puntaje,
                    default => 0.0,
                };
            } elseif ($resp === ' ') {
                $estado = 'Blanco';
                $puntos = $pond * $valBlanco;
                $blancos++;
            } elseif ($resp === $pat) {
                $estado = 'Correcto';
                $puntos = $pond * $valCorrecta;
                $correctas++;
            } else {
                $estado = 'Incorrecto';
                $puntos = $pond * $valIncorrecta;
                $incorrectas++;
            }

            $puntajeTotal += $puntos;
            $detalle[] = [
                'num' => $i + 1,
                'resp' => $resp,
                'clave' => $pat,
                'pond' => $pond,
                'estado' => $estado,
                'puntos' => $puntos,
            ];
        }

        $postulante = DB::table('postulante')->where('nro_doc', $dni)->first();

        return View::make('pruebas::ficha', [
            'prueba' => $prueba,
            'ide' => $ide,
            'dni' => $dni,
            'postulante' => $postulante,
            'multiplicador' => $multiplicador,
            'valCorrecta' => $valCorrecta,
            'valIncorrecta' => $valIncorrecta,
            'valBlanco' => $valBlanco,
            'correctas' => $correctas,
            'incorrectas' => $incorrectas,
            'blancos' => $blancos,
            'detalle' => $detalle,
            'calificado' => $calificado,
            'puntajeAlmacenado' => $puntajeAlmacenado,
            'tieneRes' => $tieneRes,
            'puntajeTotal' => $calificado ? (float) $puntajeAlmacenado : $puntajeTotal,
        ])->render();
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

    private function createMpdf(): Mpdf
    {
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        return new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 6,
            'margin_right' => 6,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'tempDir' => $tempDir,
        ]);
    }
}
