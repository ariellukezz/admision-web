<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Rename table: pabellones → ambientes
        Schema::rename('pabellones', 'ambientes');

        // 2. Rename pivot table: pabellon_programa → ambiente_programa
        Schema::table('pabellon_programa', function (Blueprint $table) {
            $table->dropForeign(['id_pabellon']);
        });
        Schema::rename('pabellon_programa', 'ambiente_programa');
        Schema::table('ambiente_programa', function (Blueprint $table) {
            $table->renameColumn('id_pabellon', 'id_ambiente');
            $table->foreign('id_ambiente')->references('id')->on('ambientes')->onDelete('cascade');
        });

        // 3. Rename FK column in aulas_gestion: id_pabellon → id_ambiente
        Schema::table('aulas_gestion', function (Blueprint $table) {
            $table->dropForeign(['id_pabellon']);
        });
        // Drop unique constraint that includes id_pabellon
        DB::statement('ALTER TABLE aulas_gestion DROP INDEX aulas_gestion_codigo_id_pabellon_unique');
        Schema::table('aulas_gestion', function (Blueprint $table) {
            $table->renameColumn('id_pabellon', 'id_ambiente');
            $table->foreign('id_ambiente')->references('id')->on('ambientes')->onDelete('cascade');
            $table->unique(['codigo', 'id_ambiente']);
        });

        // 4. Rename column in ubicacion_aula: pabellon → ambiente
        Schema::table('ubicacion_aula', function (Blueprint $table) {
            $table->renameColumn('pabellon', 'ambiente');
        });
    }

    public function down(): void
    {
        // Reverse ubicacion_aula
        Schema::table('ubicacion_aula', function (Blueprint $table) {
            $table->renameColumn('ambiente', 'pabellon');
        });

        // Reverse aulas_gestion
        Schema::table('aulas_gestion', function (Blueprint $table) {
            $table->dropForeign(['id_ambiente']);
            $table->dropUnique(['codigo', 'id_ambiente']);
            $table->renameColumn('id_ambiente', 'id_pabellon');
            $table->foreign('id_pabellon')->references('id')->on('pabellones')->onDelete('cascade');
            $table->unique(['codigo', 'id_pabellon']);
        });

        // Reverse pivot
        Schema::table('ambiente_programa', function (Blueprint $table) {
            $table->dropForeign(['id_ambiente']);
            $table->renameColumn('id_ambiente', 'id_pabellon');
        });
        Schema::rename('ambiente_programa', 'pabellon_programa');
        Schema::table('pabellon_programa', function (Blueprint $table) {
            $table->foreign('id_pabellon')->references('id')->on('pabellones')->onDelete('cascade');
        });

        // Reverse main table
        Schema::rename('ambientes', 'pabellones');
    }
};
