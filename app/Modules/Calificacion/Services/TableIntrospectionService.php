<?php

namespace App\Modules\Calificacion\Services;

use Illuminate\Support\Facades\DB;

class TableIntrospectionService
{
    /**
     * Tablas que NO se deben resolver automáticamente.
     * Evita traer columnas irrelevantes (users, roles, permissions, etc.)
     */
    private const EXCLUDED_TABLES = [
        'users',
        'roles',
        'permissions',
        'model_has_permissions',
        'model_has_roles',
        'role_has_permissions',
        'password_resets',
        'password_reset_tokens',
        'personal_access_tokens',
        'failed_jobs',
        'jobs',
        'migrations',
        'settings',
        'notifications',
        'activity_logs',
        'audit_trail',
    ];

    /**
     * Resolver relaciones foreign key recursivamente hasta maxDepth.
     */
    public function resolveRelationships(string $table, array $data, int $maxDepth = 4): array
    {
        if (empty($data)) {
            return $data;
        }

        $resolved = $this->resolveRecursive($table, $data, 0, $maxDepth, []);
        return $this->filterIds($resolved);
    }

    private function resolveRecursive(string $table, array $data, int $currentDepth, int $maxDepth, array $visited): array
    {
        if ($currentDepth >= $maxDepth || in_array($table, $visited)) {
            return $data;
        }

        $visited[] = $table;
        $foreignKeys = $this->getForeignKeys($table);

        if (empty($foreignKeys)) {
            return $data;
        }

        $resolvedValues = [];

        foreach ($foreignKeys as $fk) {
            // Saltar tablas excluidas (users, roles, etc.)
            if (in_array($fk->referenced_table, self::EXCLUDED_TABLES)) {
                continue;
            }

            $foreignValues = array_unique(
                array_filter(array_column($data, $fk->column_name), fn($v) => !is_null($v))
            );

            if (empty($foreignValues)) {
                continue;
            }

            $columns = $this->getTableColumns($fk->referenced_table);
            $selectColumns = array_filter($columns, fn($col) => $col !== $fk->referenced_column || $col === 'id_postulante');
            $select = implode(', ', array_map(fn($col) => "`$col`", $selectColumns));

            $relatedData = DB::table($fk->referenced_table)
                ->selectRaw($select . ", `$fk->referenced_column` as _id")
                ->whereIn($fk->referenced_column, $foreignValues)
                ->get()
                ->keyBy('_id')
                ->map(fn($row) => (array) $row)
                ->toArray();

            $relatedData = $this->resolveRecursive($fk->referenced_table, $relatedData, $currentDepth + 1, $maxDepth, $visited);
            $resolvedValues[$fk->referenced_table] = $relatedData;
        }

        return array_map(function ($row) use ($resolvedValues, $foreignKeys, $table) {
            $resolved = (array) $row;
            $nested = [];

            foreach ($resolved as $key => $value) {
                if (!in_array($key, array_column($foreignKeys, 'column_name'))) {
                    $nested[strtolower($table)][$key] = $value;
                }
            }

            foreach ($foreignKeys as $fk) {
                if (in_array($fk->referenced_table, self::EXCLUDED_TABLES)) {
                    continue;
                }
                $fkColumn = $fk->column_name;
                if (isset($resolved[$fkColumn]) && isset($resolvedValues[$fk->referenced_table][$resolved[$fkColumn]])) {
                    $nested[strtolower($fk->referenced_table)] = $resolvedValues[$fk->referenced_table][$resolved[$fkColumn]];
                }
            }

            return $nested;
        }, $data);
    }

    public function getForeignKeys(string $table): array
    {
        return DB::select("
            SELECT COLUMN_NAME as column_name,
                   REFERENCED_TABLE_NAME as referenced_table,
                   REFERENCED_COLUMN_NAME as referenced_column
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [config('database.connections.mysql.database'), $table]);
    }

    public function getTableColumns(string $table): array
    {
        $columns = DB::select("
            SELECT COLUMN_NAME
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ORDER BY ORDINAL_POSITION
        ", [config('database.connections.mysql.database'), $table]);

        return array_column($columns, 'COLUMN_NAME');
    }

    private function filterIds(array $data): array
    {
        return array_map(function ($row) {
            $filtered = [];
            foreach ($row as $table => $columns) {
                $filtered[$table] = $this->filterIdsRecursive($columns);
            }
            return $filtered;
        }, $data);
    }

    private function filterIdsRecursive(array $data): array
    {
        $filtered = [];
        foreach ($data as $key => $value) {
            $lowerKey = strtolower($key);
            if ((str_contains($lowerKey, 'id_') || str_contains($lowerKey, '_id') || $lowerKey === 'id') && $lowerKey !== 'id_postulante') {
                continue;
            }
            $filtered[$key] = is_array($value) ? $this->filterIdsRecursive($value) : $value;
        }
        return $filtered;
    }
    
}
