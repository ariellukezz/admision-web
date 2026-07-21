<?php

namespace App\Modules\Calificacion\Services;

use App\Modules\Calificacion\Models\ClassroomAssignment;
use App\Modules\Calificacion\Models\RedistributionEvent;
use App\Modules\Calificacion\Models\SwapLog;
use Illuminate\Support\Facades\DB;

class SwapService
{
    private const TEST_PATTERN = [
        ['P', 'S', 'Q', 'T', 'R'],
        ['Q', 'T', 'R', 'P', 'S'],
        ['R', 'P', 'S', 'Q', 'T'],
        ['S', 'Q', 'T', 'R', 'P'],
        ['T', 'R', 'P', 'S', 'Q'],
        ['P', 'S', 'Q', 'T', 'R'],
        ['Q', 'T', 'R', 'P', 'S'],
        ['R', 'P', 'S', 'Q', 'T'],
    ];

    public function performSwaps(int $filterGroupId, array $swaps, ?string $reason = null, ?int $userId = null): array
    {
        $this->validateSwaps($swaps);

        DB::beginTransaction();

        try {
            $event = RedistributionEvent::create([
                'grupo_filtro_id' => $filterGroupId,
                'usuario_id' => $userId,
                'tipo' => 'manual',
                'descripcion' => 'Swaps manuales (' . count($swaps) . ' intercambios)',
                'motivo' => $reason,
                'postulantes_count' => count($swaps) * 2,
                'aulas_count' => 0,
            ]);

            $swapLogs = [];
            $crossGroupCount = 0;

            foreach ($swaps as $index => $swap) {
                $origin = $swap['origin'];
                $destination = $swap['destination'];
                $isMove = $destination['id_postulante'] === null;

                $originAssignment = ClassroomAssignment::where('aula_id', $origin['classroom_id'])
                    ->where('posicion', $origin['position'])
                    ->first();

                $destinationAssignment = $isMove
                    ? null
                    : ClassroomAssignment::where('aula_id', $destination['classroom_id'])
                        ->where('posicion', $destination['position'])
                        ->first();

                if (!$originAssignment) {
                    DB::rollBack();
                    throw new \DomainException("Asignación de origen no encontrada en swap $index.");
                }

                if (!$isMove && !$destinationAssignment) {
                    DB::rollBack();
                    throw new \DomainException("Asignación de destino no encontrada en swap $index.");
                }

                if (!$isMove && $originAssignment->id_postulante != $origin['id_postulante']) {
                    DB::rollBack();
                    throw new \DomainException("Datos de estudiantes no coinciden en swap $index.");
                }

                $swapType = $isMove
                    ? 'move'
                    : $this->determineSwapType($origin['classroom_id'], $originAssignment->grupo_filtro_id, $destinationAssignment->grupo_filtro_id);
                if ($swapType === 'cross_group') {
                    $crossGroupCount++;
                }

                $swapLogs[] = $this->buildSwapLog($event->id, $origin, $destination, $originAssignment, $destinationAssignment, $swapType, $reason, $userId);

                $this->executeSwap($origin, $destination, $originAssignment, $destinationAssignment, $isMove);
            }

            SwapLog::insert($swapLogs);

            $event->update([
                'metadata' => [
                    'cross_group_swaps' => $crossGroupCount,
                    'same_group_swaps' => count($swaps) - $crossGroupCount,
                ],
            ]);

            DB::commit();

            return [
                'event_id' => $event->id,
                'num_swaps' => count($swaps),
                'cross_group_swaps' => $crossGroupCount,
            ];
        } catch (\DomainException $e) {
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getAuditLog(int $filterGroupId): array
    {
        $events = RedistributionEvent::where('grupo_filtro_id', $filterGroupId)
            ->with(['swapLogs' => fn($q) => $q->orderBy('created_at', 'desc')])
            ->orderBy('created_at', 'desc')
            ->get();

        return ['events' => $events->map(fn($event) => $this->formatEvent($event))->toArray()];
    }

    private function validateSwaps(array $swaps): void
    {
        $seenPairs = [];
        foreach ($swaps as $index => $swap) {
            $originId = $swap['origin']['id_postulante'];
            $destId = $swap['destination']['id_postulante'];

            // Solo validar duplicados si ambos tienen postulante
            if ($originId !== null && $destId !== null) {
                $key = min($originId, $destId) . '-' . max($originId, $destId);
                if (isset($seenPairs[$key])) {
                    throw new \DomainException("Swap duplicado en índice $index: mismo par de estudiantes.");
                }
                $seenPairs[$key] = true;

                if ($originId === $destId) {
                    throw new \DomainException("Swap inválido en índice $index: mismo estudiante.");
                }
            }

            if ($originId === null) {
                throw new \DomainException("Swap inválido en índice $index: el origen debe tener un estudiante.");
            }
        }
    }

    private function determineSwapType(int $originClassroomId, int $originFilterGroupId, int $destinationFilterGroupId): string
    {
        if ($originClassroomId === request()->input('swaps.0.destination.classroom_id')) {
            return 'same_classroom';
        }
        return $originFilterGroupId !== $destinationFilterGroupId ? 'cross_group' : 'same_group';
    }

    private function getTestTypeForPosition(int $position): string
    {
        $row = ($position - 1) % 8;
        $col = intdiv($position - 1, 8);
        return self::TEST_PATTERN[$row][$col] ?? 'P';
    }

    private function executeSwap(array $origin, array $destination, $originAssignment, $destinationAssignment, bool $isMove = false): void
    {
        if ($isMove) {
            // Mover a celda vacía: el test_type pertenece a la posición destino
            $destTestType = $this->getTestTypeForPosition($destination['position']);

            ClassroomAssignment::where('aula_id', $origin['classroom_id'])
                ->where('posicion', $origin['position'])
                ->update([
                    'aula_id' => $destination['classroom_id'],
                    'posicion' => $destination['position'],
                    'tipo_examen' => $destTestType,
                    'updated_at' => now(),
                ]);
            return;
        }

        // Swap: intercambiar solo los datos del estudiante, NO el test_type (pertenece a la posición)
        ClassroomAssignment::where('aula_id', $origin['classroom_id'])
            ->where('posicion', $origin['position'])
            ->update([
                'id_postulante' => $destinationAssignment->id_postulante,
                'codigo' => $destinationAssignment->codigo,
                'grupo_filtro_id' => $destinationAssignment->grupo_filtro_id,
                'updated_at' => now(),
            ]);

        ClassroomAssignment::where('aula_id', $destination['classroom_id'])
            ->where('posicion', $destination['position'])
            ->update([
                'id_postulante' => $originAssignment->id_postulante,
                'codigo' => $originAssignment->codigo,
                'grupo_filtro_id' => $originAssignment->grupo_filtro_id,
                'updated_at' => now(),
            ]);
    }

    private function buildSwapLog(int $eventId, array $origin, array $destination, $originAssignment, $destinationAssignment, string $swapType, ?string $reason, ?int $userId = null): array
    {
        return [
            'evento_id' => $eventId,
            'usuario_id' => $userId,
            'origen_grupo_filtro_id' => $originAssignment->grupo_filtro_id,
            'origen_aula_id' => $origin['classroom_id'],
            'origen_posicion' => $origin['position'],
            'origen_id_postulante_anterior' => $originAssignment->id_postulante,
            'origen_codigo_anterior' => $originAssignment->codigo,
            'origen_tipo_examen_anterior' => $originAssignment->tipo_examen,
            'destino_grupo_filtro_id' => $destinationAssignment?->grupo_filtro_id ?? $originAssignment->grupo_filtro_id,
            'destino_aula_id' => $destination['classroom_id'],
            'destino_posicion' => $destination['position'],
            'destino_id_postulante_anterior' => $destinationAssignment?->id_postulante,
            'destino_codigo_anterior' => $destinationAssignment?->codigo ?? '',
            'destino_tipo_examen_anterior' => $destinationAssignment?->tipo_examen,
            'tipo_intercambio' => $swapType,
            'motivo' => $reason,
            'estado' => 'completed',
            'created_at' => now(),
        ];
    }

    private function formatEvent($event): array
    {
        return [
            'id' => $event->id,
            'type' => $event->tipo,
            'description' => $event->descripcion,
            'reason' => $event->motivo,
            'postulantes_count' => $event->postulantes_count,
            'classrooms_count' => $event->aulas_count,
            'metadata' => $event->metadata,
            'created_at' => $event->created_at,
            'swaps' => $event->tipo === 'manual' ? $event->swapLogs->map(fn($log) => [
                'origin' => [
                    'filter_group_id' => $log->origen_grupo_filtro_id,
                    'classroom_id' => $log->origen_aula_id,
                    'position' => $log->origen_posicion,
                    'id_postulante' => $log->origen_id_postulante_anterior,
                    'code' => $log->origen_codigo_anterior,
                    'test_type' => $log->origen_tipo_examen_anterior,
                ],
                'destination' => [
                    'filter_group_id' => $log->destino_grupo_filtro_id,
                    'classroom_id' => $log->destino_aula_id,
                    'position' => $log->destino_posicion,
                    'id_postulante' => $log->destino_id_postulante_anterior,
                    'code' => $log->destino_codigo_anterior,
                    'test_type' => $log->destino_tipo_examen_anterior,
                ],
                'swap_type' => $log->tipo_intercambio,
                'status' => $log->estado,
                'created_at' => $log->created_at,
            ])->toArray() : [],
        ];
    }
}
