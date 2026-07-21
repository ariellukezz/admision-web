<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aulas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grupo_filtro_id');
            $table->string('nombre', 100); // "Salon 1", "Salon 2"
            
            // Capacidad flexible del salón (puede variar por salón)
            $table->unsignedTinyInteger('capacidad')->default(40);
            
            // Cuenta actual de estudiantes asignados (para validación rápida)
            $table->unsignedTinyInteger('contador_actual')->default(0);
            
            $table->timestamps();
            
            // Foreign key
            $table->foreign('grupo_filtro_id')
                  ->references('id')
                  ->on('grupos_filtro')
                  ->onDelete('cascade');
            
            // Índices
            $table->index('grupo_filtro_id');
            $table->index(['grupo_filtro_id', 'nombre'], 'idx_filter_group_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aulas');
    }
};