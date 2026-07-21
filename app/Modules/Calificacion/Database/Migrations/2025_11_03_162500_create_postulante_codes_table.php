<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('codigos_postulantes')) {
            Schema::create('codigos_postulantes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('grupo_filtro_id');
                $table->integer('id_postulante'); // INT SIGNED
                $table->integer('id_modalidad'); // INT SIGNED
                $table->integer('id_proceso');   // INT SIGNED
                $table->string('codigo', 255);
                $table->timestamps();

                // Índice único para evitar duplicados
                $table->unique(['id_postulante', 'id_modalidad', 'id_proceso'], 'unique_postulante_proceso');

                // Índice para exclusión rápida
                $table->index(['id_modalidad', 'id_proceso', 'id_postulante'], 'idx_exclusion');

                // Foreign keys (solo si existen las tablas referenciadas)
                if (Schema::hasTable('grupos_filtro')) {
                    $table->foreign('grupo_filtro_id')
                        ->references('id')
                        ->on('grupos_filtro')
                        ->onDelete('cascade');
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('codigos_postulantes');
    }
};
