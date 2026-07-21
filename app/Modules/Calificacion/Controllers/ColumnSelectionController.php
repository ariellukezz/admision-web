<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Models\ColumnSelection;
use App\Modules\Calificacion\Requests\StoreColumnSelectionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ColumnSelectionController extends BaseCalificacionController
{
    public function store(StoreColumnSelectionRequest $request): JsonResponse
    {
        $tableName = $request->input('table_name');
        $selectedColumns = array_unique($request->input('selected_columns'));

        if (empty($selectedColumns)) {
            return $this->errorResponse('Debe seleccionar al menos una columna', 422);
        }

        sort($selectedColumns);
        $columnsHash = Hash::make(implode(',', $selectedColumns) . $tableName);

        $exists = ColumnSelection::where('nombre_tabla', $tableName)
            ->where('hash_columnas', $columnsHash)
            ->exists();

        if ($exists) {
            return $this->errorResponse('La selección ya existe para esta tabla', 409);
        }

        ColumnSelection::create([
            'usuario_id' => null,
            'nombre_tabla' => $tableName,
            'columnas_seleccionadas' => $selectedColumns,
            'hash_columnas' => $columnsHash,
        ]);

        return $this->successResponse(
            [
                'table_name' => $tableName,
                'selected_columns_count' => count($selectedColumns),
            ],
            'Selección guardada correctamente',
            201
        );
    }

    public function last(Request $request): JsonResponse
    {
        $request->validate([
            'table_name' => 'required|string|max:255',
        ]);

        $lastSelection = ColumnSelection::where('nombre_tabla', $request->input('table_name'))
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastSelection) {
            return $this->errorResponse('No se encontró ninguna selección para esta tabla', 404);
        }

        return $this->successResponse([
            'table_name' => $lastSelection->nombre_tabla,
            'selected_columns' => $lastSelection->columnas_seleccionadas,
            'created_at' => $lastSelection->created_at,
        ]);
    }
}
