<?php

namespace App\Modules\Pruebas\Services;

use App\Modules\Pruebas\Models\Prueba;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PruebaService
{
    public function index(?string $term = null, int $perPage = 10): LengthAwarePaginator
    {
        return Prueba::with(['ponderacion:id,nombre', 'multiplicador:id,correcta,incorrecta,blanco'])
            ->withCount(['ides', 'respuestas', 'tipos'])
            ->when($term, fn($q) => $q->where('nombre', 'LIKE', "%{$term}%"))
            ->orderBy('id', 'DESC')
            ->paginate($perPage);
    }

    public function store(array $data): Prueba
    {
        return Prueba::create([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'id_ponderacion' => $data['id_ponderacion'] ?? null,
            'id_multiplicador' => $data['id_multiplicador'] ?? null,
            'estado' => $data['estado'] ?? true,
        ]);
    }

    public function show(int $id): Prueba
    {
        $prueba = Prueba::with(['ponderacion:id,nombre', 'multiplicador:id,correcta,incorrecta,blanco'])
            ->find($id);

        if (!$prueba) {
            throw new \DomainException('Prueba no encontrada');
        }

        return $prueba;
    }

    public function update(int $id, array $data): Prueba
    {
        $prueba = $this->show($id);

        $prueba->update([
            'nombre' => $data['nombre'] ?? $prueba->nombre,
            'descripcion' => $data['descripcion'] ?? $prueba->descripcion,
            'id_ponderacion' => $data['id_ponderacion'] ?? $prueba->id_ponderacion,
            'id_multiplicador' => $data['id_multiplicador'] ?? $prueba->id_multiplicador,
            'estado' => $data['estado'] ?? $prueba->estado,
        ]);

        return $prueba->fresh();
    }

    public function destroy(int $id): void
    {
        $prueba = $this->show($id);
        $prueba->delete();
    }
}
