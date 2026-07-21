<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_aulas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('aula_id');
            
            // IMPORTANTE: Guardar también grupo_filtro_id para swaps cross-group
            $table->unsignedBigInteger('grupo_filtro_id');
            
            $table->integer('id_postulante'); // Signed integer para coincidir con POSTULANTE
            $table->string('codigo', 50);
            $table->unsignedTinyInteger('posicion'); // 1-40 (o hasta capacidad del salón)
            $table->char('tipo_examen', 1); // P, Q, R, S, T
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('aula_id')
                  ->references('id')
                  ->on('aulas')
                  ->onDelete('cascade');
            
            $table->foreign('grupo_filtro_id')
                  ->references('id')
                  ->on('grupos_filtro')
                  ->onDelete('cascade');
            
            // Índices
            $table->unique(['aula_id', 'posicion']); // No duplicar posiciones
            $table->index('codigo', 'idx_code');
            $table->index('tipo_examen', 'idx_test_type');
            $table->index('id_postulante', 'idx_postulante');
            $table->index('grupo_filtro_id', 'idx_filter_group');
            $table->index(['aula_id', 'tipo_examen'], 'idx_classroom_test');
            $table->index(['grupo_filtro_id', 'id_postulante'], 'idx_filter_postulante');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_aulas');
    }
};