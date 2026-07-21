<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create examen_tipos table
        Schema::create('examen_tipos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_examen_simulacro');
            $table->foreign('id_examen_simulacro')->references('id')->on('examen_simulacro')->onDelete('cascade');
            $table->char('tipo', 1)->nullable();
            $table->string('respuestas', 90)->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        // 2. Migrate existing tipos from examen_simulacro to examen_tipos
        $existentes = DB::table('examen_simulacro')->get();
        foreach ($existentes as $row) {
            DB::table('examen_tipos')->insert([
                'id_examen_simulacro' => $row->id,
                'tipo' => $row->tipo,
                'respuestas' => null,
                'estado' => $row->estado ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Add config columns to examen_simulacro
        Schema::table('examen_simulacro', function (Blueprint $table) {
            $table->integer('n_preguntas')->default(60)->after('area');
            $table->integer('n_alternativas')->default(5)->after('n_preguntas');
            $table->unsignedBigInteger('id_ponderacion')->nullable()->after('n_alternativas');
        });

        // 4. Drop tipo column from examen_simulacro (now in examen_tipos)
        Schema::table('examen_simulacro', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }

    public function down(): void
    {
        // Restore tipo column
        Schema::table('examen_simulacro', function (Blueprint $table) {
            $table->char('tipo', 1)->nullable()->after('id');
        });

        // Restore tipos from examen_tipos
        $tipos = DB::table('examen_tipos')->get();
        foreach ($tipos as $tipo) {
            DB::table('examen_simulacro')
                ->where('id', $tipo->id_examen_simulacro)
                ->update(['tipo' => $tipo->tipo]);
        }

        // Drop config columns
        Schema::table('examen_simulacro', function (Blueprint $table) {
            $table->dropColumn(['n_preguntas', 'n_alternativas', 'id_ponderacion']);
        });

        Schema::dropIfExists('examen_tipos');
    }
};
