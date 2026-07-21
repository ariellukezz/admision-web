<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Models\UbicacionAula;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UbicacionAulaController extends BaseCalificacionController
{
    public function index(Request $request): JsonResponse
    {
        $query = UbicacionAula::query();

        if ($request->has('area') && $request->input('area') !== '') {
            $query->where('area', $request->input('area'));
        }

        if ($request->has('pabellon') && $request->input('pabellon') !== '') {
            $query->where('pabellon', 'like', '%' . $request->input('pabellon') . '%');
        }

        $aulas = $query->orderBy('area')->orderBy('codigo')->get();

        return $this->successResponse($aulas);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codigo' => 'required|string|max:50|unique:ubicacion_aula,codigo',
            'pabellon' => 'required|string|max:100',
            'piso' => 'required|string|max:10',
            'capacidad' => 'required|integer|min:1|max:500',
            'area' => 'nullable|string|max:50',
        ]);

        $aula = UbicacionAula::create($data);

        return $this->successResponse($aula, 'Aula creada correctamente', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $aula = UbicacionAula::find($id);

        if (!$aula) {
            return $this->errorResponse('Aula no encontrada', 404);
        }

        $data = $request->validate([
            'codigo' => 'sometimes|string|max:50|unique:ubicacion_aula,codigo,' . $id,
            'pabellon' => 'sometimes|string|max:100',
            'piso' => 'sometimes|string|max:10',
            'capacidad' => 'sometimes|integer|min:1|max:500',
            'area' => 'nullable|string|max:50',
        ]);

        $aula->update($data);

        return $this->successResponse($aula, 'Aula actualizada correctamente');
    }

    public function destroy(int $id): JsonResponse
    {
        $aula = UbicacionAula::find($id);

        if (!$aula) {
            return $this->errorResponse('Aula no encontrada', 404);
        }

        $aula->delete();

        return $this->successResponse(null, 'Aula eliminada correctamente');
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'data' => 'required|string',
            'area' => 'nullable|string|max:50',
        ]);

        $lines = preg_split('/\r?\n/', trim($request->input('data')));
        $area = $request->input('area');
        $imported = 0;
        $updated = 0;
        $errors = [];

        // Patrones de piso conocidos
        $pisoPattern = '/^(1er|2do|3er|4to|5to)$/i';

        foreach ($lines as $i => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Parsear: codigo pabellon... piso capacidad
            $parts = preg_split('/\s+/', $line);
            if (count($parts) < 4) {
                $errors[] = "Línea " . ($i + 1) . ": formato inválido - '{$line}'";
                continue;
            }

            $codigo = $parts[0];
            $capacidad = (int) $parts[count($parts) - 1];
            $piso = $parts[count($parts) - 2];
            $pabellon = implode(' ', array_slice($parts, 1, count($parts) - 3));

            // Normalizar piso
            $pisoLower = strtolower($piso);
            if (!preg_match($pisoPattern, $pisoLower)) {
                // Si el penúltimo no es piso, asumir que el último es piso y no hay capacidad
                $piso = $parts[count($parts) - 1];
                $capacidad = 40;
                $pabellon = implode(' ', array_slice($parts, 1, count($parts) - 2));
            }

            $pisoNorm = $this->normalizePiso($piso);

            try {
                $existing = UbicacionAula::where('codigo', $codigo)->first();
                if ($existing) {
                    $existing->update([
                        'pabellon' => trim($pabellon),
                        'piso' => $pisoNorm,
                        'capacidad' => $capacidad,
                        'area' => $area ?? $existing->area,
                    ]);
                    $updated++;
                } else {
                    UbicacionAula::create([
                        'codigo' => $codigo,
                        'pabellon' => trim($pabellon),
                        'piso' => $pisoNorm,
                        'capacidad' => $capacidad,
                        'area' => $area,
                    ]);
                    $imported++;
                }
            } catch (\Exception $e) {
                $errors[] = "Línea " . ($i + 1) . " ({$codigo}): " . $e->getMessage();
            }
        }

        return $this->successResponse([
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors,
        ], "Importación completada: {$imported} nuevas, {$updated} actualizadas" . (count($errors) > 0 ? ", " . count($errors) . " errores" : ''));
    }

    public function destroyAll(): JsonResponse
    {
        UbicacionAula::query()->delete();
        return $this->successResponse(null, 'Todas las ubicaciones eliminadas');
    }

    public function areas(): JsonResponse
    {
        $areas = UbicacionAula::select('area')
            ->distinct()
            ->whereNotNull('area')
            ->where('area', '!=', '')
            ->orderBy('area')
            ->pluck('area');

        return $this->successResponse($areas);
    }

    private function normalizePiso(string $piso): string
    {
        $map = [
            '1er' => '1er', '1' => '1er', 'primero' => '1er',
            '2do' => '2do', '2' => '2do', 'segundo' => '2do',
            '3er' => '3er', '3' => '3er', 'tercero' => '3er',
            '4to' => '4to', '4' => '4to', 'cuarto' => '4to',
            '5to' => '5to', '5' => '5to', 'quinto' => '5to',
        ];

        return $map[strtolower(trim($piso))] ?? $piso;
    }
}
