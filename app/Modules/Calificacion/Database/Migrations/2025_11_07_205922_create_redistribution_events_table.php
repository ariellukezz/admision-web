<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla para auditoría de redistribuciones
     */
    public function up(): void
    {
        Schema::create('eventos_redistribucion', function (Blueprint $table) {
            $table->id();
            
            // Grupo de filtros redistribuido
            $table->unsignedBigInteger('grupo_filtro_id');
            
            // Usuario que ejecutó (nullable porque NO tienes autenticación)
            $table->unsignedBigInteger('usuario_id')->nullable();
            
            // Tipo de redistribución
            $table->enum('tipo', [
                'initial',      // Primera distribución generada
                'aleatoria',    // Redistribución aleatoria completa
                'manual',       // Swaps manuales individuales
                'partial'       // Redistribución parcial
            ])->default('aleatoria');
            
            // Estadísticas
            $table->unsignedInteger('postulantes_count')->default(0);
            $table->unsignedSmallInteger('aulas_count')->default(0);
            $table->unsignedTinyInteger('capacidad_por_aula')->default(40);
            
            // Descripción/razón
            $table->string('descripcion', 255);
            $table->text('motivo')->nullable();
            
            // Metadata adicional (JSON)
            // Ejemplo:
            // {
            //   "execution_time_ms": 1500,
            //   "algorithm": "shuffle",
            //   "modalidades": [1, 2],
            //   "capacity_exceptions": [
            //     {"classroom_id": 5, "capacity": 38, "reason": "Salón pequeño"},
            //     {"classroom_id": 7, "capacity": 42, "reason": "Capacidad extra"}
            //   ],
            //   "cross_group_swaps": 3
            // }
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Foreign key
            $table->foreign('grupo_filtro_id')
                  ->references('id')
                  ->on('grupos_filtro')
                  ->onDelete('cascade');
            
            // Índices
            $table->index('grupo_filtro_id');
            $table->index('tipo');
            $table->index('created_at');
            $table->index(['grupo_filtro_id', 'created_at'], 'idx_filter_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos_redistribucion');
    }
};