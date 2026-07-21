<?php

namespace App\Modules\Calificacion\Services;

use App\Modules\Calificacion\Models\Area;
use App\Modules\Calificacion\Models\Asignatura;
use App\Modules\Calificacion\Models\Multiplicador;
use App\Modules\Calificacion\Models\Ponderacion;
use App\Modules\Calificacion\Models\PonderacionDetalle;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Mpdf\Mpdf;

class PonderacionService
{
    public function getPonderaciones(?string $term = null, int $perPage = 10)
    {
        return Ponderacion::select('id', 'nombre', 'total_preguntas', 'total_ponderacion', 'estado', 'id_multiplicador')
            ->when($term, fn($q) => $q->where('nombre', 'LIKE', "%{$term}%"))
            ->orderBy('id', 'DESC')
            ->paginate($perPage);
    }

    public function save(array $data): Ponderacion
    {
        return Ponderacion::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'nombre' => $data['nombre'],
                'estado' => $data['estado'] ?? true,
                'id_multiplicador' => $data['id_multiplicador'] ?? null,
            ]
        );
    }

    public function delete(int $id): void
    {
        $ponderacion = Ponderacion::find($id);
        if (!$ponderacion) {
            throw new \DomainException('Ponderación no encontrada');
        }
        PonderacionDetalle::where('id_ponderacion_simulacro', $id)->delete();
        $ponderacion->delete();
    }

    public function duplicar(int $id): Ponderacion
    {
        $original = Ponderacion::with('detalles')->find($id);
        if (!$original) {
            throw new \DomainException('Ponderación no encontrada');
        }

        DB::beginTransaction();
        try {
            $nueva = Ponderacion::create([
                'nombre' => $original->nombre . ' (Copia)',
                'estado' => true,
                'id_multiplicador' => $original->id_multiplicador,
            ]);

            foreach ($original->detalles as $detalle) {
                PonderacionDetalle::create([
                    'asignatura' => $detalle->asignatura,
                    'numero' => $detalle->numero,
                    'ponderacion' => $detalle->ponderacion,
                    'id_ponderacion_simulacro' => $nueva->id,
                    'id_asignatura' => $detalle->id_asignatura,
                    'cantidad_preguntas' => $detalle->cantidad_preguntas,
                    'subtotal' => $detalle->subtotal,
                ]);
            }

            DB::commit();
            return $nueva;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getDetalle(int $id)
    {
        return PonderacionDetalle::where('id_ponderacion_simulacro', $id)
            ->orderBy('numero', 'ASC')
            ->get();
    }

    public function saveDetalle(int $idPonderacion, array $detalles): void
    {
        $ponderacion = Ponderacion::find($idPonderacion);
        if (!$ponderacion) {
            throw new \DomainException('Ponderación no encontrada');
        }

        DB::beginTransaction();
        try {
            PonderacionDetalle::where('id_ponderacion_simulacro', $idPonderacion)->delete();

            $numero = 1;
            foreach ($detalles as $item) {
                $cantidad = (int) $item['cantidad_preguntas'];
                $pond = (float) $item['ponderacion'];
                $subtotal = $cantidad * $pond;

                $idAsignatura = $item['id_asignatura'] ?? null;
                if (!$idAsignatura) {
                    $asig = Asignatura::where('nombre', $item['asignatura'])->first();
                    $idAsignatura = $asig?->id;
                }

                PonderacionDetalle::create([
                    'asignatura' => $item['asignatura'] ?? '',
                    'numero' => $numero,
                    'ponderacion' => $pond,
                    'id_ponderacion_simulacro' => $idPonderacion,
                    'id_asignatura' => $idAsignatura,
                    'cantidad_preguntas' => $cantidad,
                    'subtotal' => $subtotal,
                ]);
                $numero++;
            }

            $totalPreguntas = collect($detalles)->sum('cantidad_preguntas');
            $totalPonderacion = collect($detalles)
                ->sum(fn($d) => (int)$d['cantidad_preguntas'] * (float)$d['ponderacion']);

            $ponderacion->update([
                'total_preguntas' => $totalPreguntas,
                'total_ponderacion' => $totalPonderacion,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getSelect(?string $term = null, int $perPage = 10)
    {
        return Ponderacion::select('id as key', 'nombre as value')
            ->when($term, fn($q) => $q->where('nombre', 'LIKE', "%{$term}%"))
            ->orderBy('id', 'DESC')
            ->paginate($perPage);
    }

    public function getAreas()
    {
        return Area::where('estado', true)->orderBy('id', 'ASC')->get();
    }

    public function getAsignaturas()
    {
        return Asignatura::where('estado', true)->orderBy('orden', 'ASC')->get();
    }

    public function generateDetallePdf(int $id): Response
    {
        $ponderacion = Ponderacion::find($id);
        if (!$ponderacion) {
            throw new \DomainException('Ponderación no encontrada');
        }

        $detalles = $this->getDetalle($id);
        $multiplicador = $ponderacion->id_multiplicador
            ? Multiplicador::find($ponderacion->id_multiplicador)
            : null;

        $html = View::make('calificacion::pdf.ponderacion-detalle', [
            'ponderacion' => $ponderacion,
            'detalles' => $detalles,
            'multiplicador' => $multiplicador,
            'totalPreguntas' => $ponderacion->total_preguntas,
            'totalPonderacion' => $ponderacion->total_ponderacion,
        ])->render();

        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'tempDir' => $tempDir,
        ]);

        $mpdf->SetTitle('Detalle de Ponderación - ' . $ponderacion->nombre);
        $mpdf->SetAuthor('Sistema de Admisión UNAP');
        $mpdf->WriteHTML($html);

        $filename = 'detalle_ponderacion_' . $id . '_' . date('YmdHis') . '.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
