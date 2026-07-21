<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Services\TableIntrospectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TableController extends BaseCalificacionController
{
    public function __construct(
        private readonly TableIntrospectionService $introspectionService
    ) {}

    public function index(): JsonResponse
    {
        $tables = DB::select("
            SELECT TABLE_NAME as name, TABLE_COMMENT as comment
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'
            ORDER BY TABLE_NAME
        ", [config('database.connections.mysql.database')]);

        return $this->successResponse(collect($tables)->map(fn($t) => [
            'name' => $t->name,
            'comment' => $t->comment,
        ]));
    }

    public function show(string $table, Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 100), 1000);
        $offset = max($request->get('offset', 0), 0);
        $depth = min($request->get('depth', 4), 4);

        $data = DB::table($table)->limit($limit)->offset($offset)->get()->toArray();
        $resolvedData = $this->introspectionService->resolveRelationships($table, $data, $depth);
        $total = DB::table($table)->count();

        return $this->successResponse($resolvedData, '', 200, [
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $total,
        ]);
    }

    public function columns(string $table): JsonResponse
    {
        $columns = DB::select("
            SELECT COLUMN_NAME as name, DATA_TYPE as type, IS_NULLABLE as nullable,
                   COLUMN_DEFAULT as default_value, COLUMN_COMMENT as comment
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ORDER BY ORDINAL_POSITION
        ", [config('database.connections.mysql.database'), $table]);

        return $this->successResponse($columns);
    }

    public function mostRelated(Request $request): JsonResponse
    {
        $mostRelatedTable = 'inscripciones';

        $limit = min($request->get('limit', 100), 1000);
        $offset = max($request->get('offset', 0), 0);
        $idProceso = $request->get('proceso');

        $query = DB::table($mostRelatedTable)->where('estado', 0);

        if ($idProceso) {
            $query->where('id_proceso', $idProceso);
        }

        $total = $query->count();
        $data = $query->limit($limit)->offset($offset)->get()->toArray();
        $resolvedData = $this->introspectionService->resolveRelationships($mostRelatedTable, $data, 4);

        return $this->successResponse($resolvedData, '', 200, [
            'table' => $mostRelatedTable,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $total,
            'id_proceso' => $idProceso,
        ]);
    }

    public function procesos(): JsonResponse
    {
        $procesos = DB::table('procesos')
            ->join('inscripciones', 'procesos.id', '=', 'inscripciones.id_proceso')
            ->where('inscripciones.estado', 0)
            ->distinct()
            ->select('procesos.id as id_proceso', 'procesos.nombre as proceso')
            ->orderBy('procesos.nombre')
            ->get();

        return $this->successResponse($procesos);
    }

    public function modalidades(): JsonResponse
    {
        $modalidades = DB::table('modalidad')
            ->select('id as id_modalidad', 'codigo as cod_modalidad', 'nombre as modalidad')
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return $this->successResponse($modalidades);
    }

    public function areas(): JsonResponse
    {
        $areas = DB::table('areas')
            ->select('id as id_area', 'nombre as area')
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return $this->successResponse($areas);
    }

    public function programas(Request $request): JsonResponse
    {
        $query = DB::table('programa')
            ->leftJoin('areas', 'programa.id_area', '=', 'areas.id')
            ->select(
                'programa.id as id_programa',
                'programa.codigo as cod_programa',
                'programa.nombre as programa_estudios',
                'programa.id_area',
                'programa.area as area'
            );

        if ($request->has('id_area')) {
            $query->where('programa.id_area', $request->id_area);
        }

        return $this->successResponse($query->orderBy('programa.nombre')->get());
    }
}
