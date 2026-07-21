<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla para logs de intercambios manuales entre estudiantes
     * SOPORTA SWAPS CROSS-GROUP (entre diferentes filter_groups)
     */
    public function up(): void
    {
        Schema::create('registros_intercambios', function (Blueprint $table) {
            $table->id();
            
            // Evento de redistribución al que pertenece
            $table->unsignedBigInteger('evento_id');
            
            // Usuario que realizó el swap (nullable)
            $table->unsignedBigInteger('usuario_id')->nullable();
            
            // ========================================
            // ORIGEN (Estudiante 1 - ANTES del swap)
            // ========================================
            $table->unsignedBigInteger('origen_grupo_filtro_id'); // Para swaps cross-group
            $table->unsignedBigInteger('origen_aula_id');
            $table->unsignedTinyInteger('origen_posicion');
            $table->integer('origen_id_postulante_anterior');
            $table->string('origen_codigo_anterior', 50);
            $table->char('origen_tipo_examen_anterior', 1)->nullable();
            
            // ========================================
            // DESTINO (Estudiante 2 - ANTES del swap)
            // ========================================
            $table->unsignedBigInteger('destino_grupo_filtro_id'); // Para swaps cross-group
            $table->unsignedBigInteger('destino_aula_id');
            $table->unsignedTinyInteger('destino_posicion');
            $table->integer('destino_id_postulante_anterior');
            $table->string('destino_codigo_anterior', 50);
            $table->char('destino_tipo_examen_anterior', 1)->nullable();
            
            // ========================================
            // TIPO DE INTERCAMBIO
            // ========================================
            $table->enum('tipo_intercambio', [
                'same_classroom',    // Mismo salón (cambio de posición)
                'same_group',        // Mismo filter_group, diferentes salones
                'cross_group'        // Diferentes filter_groups (NUEVO)
            ])->default('same_group');
            
            // ========================================
            // JUSTIFICACIÓN Y METADATA
            // ========================================
            $table->text('motivo')->nullable();
            
            // Metadata (JSON):
            // {
            //   "ip_address": "192.168.1.100",
            //   "detected_conflict": "same_college",
            //   "auto_suggestion": true,
            //   "origin_modalidad": 1,
            //   "destination_modalidad": 2,
            //   "capacity_validated": true
            // }
            $table->json('metadata')->nullable();
            
            // Estado del swap
            $table->enum('estado', ['completed', 'reverted'])->default('completed');
            
            // Si fue revertido, ID del swap que lo revirtió
            $table->unsignedBigInteger('revertido_por_intercambio_id')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('fecha_reversion')->nullable();
            
            // ========================================
            // FOREIGN KEYS
            // ========================================
            $table->foreign('evento_id')
                  ->references('id')
                  ->on('eventos_redistribucion')
                  ->onDelete('cascade');
            
            $table->foreign('origen_grupo_filtro_id')
                  ->references('id')
                  ->on('grupos_filtro')
                  ->onDelete('cascade');
            
            $table->foreign('origen_aula_id')
                  ->references('id')
                  ->on('aulas')
                  ->onDelete('cascade');
            
            $table->foreign('destino_grupo_filtro_id')
                  ->references('id')
                  ->on('grupos_filtro')
                  ->onDelete('cascade');
            
            $table->foreign('destino_aula_id')
                  ->references('id')
                  ->on('aulas')
                  ->onDelete('cascade');
            
            $table->foreign('revertido_por_intercambio_id')
                  ->references('id')
                  ->on('registros_intercambios')
                  ->onDelete('set null');
            
            // ========================================
            // ÍNDICES
            // ========================================
            $table->index('evento_id');
            $table->index(['origen_id_postulante_anterior', 'destino_id_postulante_anterior'], 'idx_postulantes');
            $table->index('origen_grupo_filtro_id');
            $table->index('destino_grupo_filtro_id');
            $table->index('tipo_intercambio');
            $table->index('estado');
            $table->index('created_at');
            
            // Índice compuesto para búsquedas cross-group
            $table->index([
                'tipo_intercambio', 
                'origen_grupo_filtro_id', 
                'destino_grupo_filtro_id'
            ], 'idx_cross_group_swaps');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_intercambios');
    }
};