<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar columnas a inscripciones
        if (!Schema::hasColumn('inscripciones', 'codigo')) {
            Schema::table('inscripciones', function (Blueprint $table) {
                $table->string('codigo', 255)->nullable()->after('estado');
                $table->unsignedBigInteger('grupo_filtro_id')->nullable()->after('codigo');
            });
        }

        // Migrar datos existentes desde codigos_postulantes
        if (Schema::hasTable('codigos_postulantes')) {
            $codes = DB::table('codigos_postulantes')->get();

            foreach ($codes as $row) {
                DB::table('inscripciones')
                    ->where('id_postulante', $row->id_postulante)
                    ->where('id_modalidad', $row->id_modalidad)
                    ->where('id_proceso', $row->id_proceso)
                    ->update([
                        'codigo' => $row->codigo,
                        'grupo_filtro_id' => $row->grupo_filtro_id,
                    ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('inscripciones', 'codigo')) {
            Schema::table('inscripciones', function (Blueprint $table) {
                $table->dropColumn(['codigo', 'grupo_filtro_id']);
            });
        }
    }
};
