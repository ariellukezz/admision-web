<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Añadir columna JSON
        Schema::table('grupos_filtro', function (Blueprint $table) {
            // Añadir columna JSON para múltiples modalidades
            $table->json('id_modalidades')->nullable()->after('id_modalidad');
        });

        // 2) Migrar datos existentes: id_modalidad => id_modalidades (JSON_ARRAY)
        // Usamos DB::statement/DB::raw para ejecutar la función JSON_ARRAY en MySQL
        DB::statement("
            UPDATE grupos_filtro
            SET id_modalidades = JSON_ARRAY(id_modalidad)
            WHERE id_modalidad IS NOT NULL
        ");

        // 3) Crear columna generada que extrae el primer elemento y añadir índice
        // Nota: storedAs() crea una columna generada STORED (requiere MySQL 5.7+).
        // La expresión convierte a entero para permitir índices numéricos.
        Schema::table('grupos_filtro', function (Blueprint $table) {
            // columna generada con el primer elemento del array JSON
            // CAST(JSON_UNQUOTE(JSON_EXTRACT(id_modalidades, '$[0]')) AS UNSIGNED)
            // storedAs soporta la expresión SQL
            $table->unsignedInteger('id_modalidad_primary')
                  ->nullable()
                  ->storedAs("CAST(JSON_UNQUOTE(JSON_EXTRACT(id_modalidades, '$[0]')) AS UNSIGNED)");

            // índice sobre la columna generada
            $table->index('id_modalidad_primary', 'idx_modalidad_primary');
        });

        // Nota: si tu intención es consultas por JSON_CONTAINS para cualquiera de las modalidades,
        // tendrás que crear columnas generadas adicionales o usar una tabla relacional.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grupos_filtro', function (Blueprint $table) {
            // Borrar índice y columna generada (si existen)
            if (Schema::hasColumn('grupos_filtro', 'id_modalidad_primary')) {
                // Laravel no expone método para verificar índice; intentamos eliminar si existe
                try {
                    $table->dropIndex('idx_modalidad_primary');
                } catch (\Exception $e) {
                    // ignorar si no existe
                }
            }

            if (Schema::hasColumn('grupos_filtro', 'id_modalidad_primary')) {
                $table->dropColumn('id_modalidad_primary');
            }

            if (Schema::hasColumn('grupos_filtro', 'id_modalidades')) {
                $table->dropColumn('id_modalidades');
            }
        });
    }
};