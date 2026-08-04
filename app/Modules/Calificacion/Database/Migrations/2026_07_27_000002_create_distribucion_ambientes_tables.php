<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribucion_ambientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_proceso')->nullable();
            $table->unsignedSmallInteger('total_estudiantes');
            $table->unsignedSmallInteger('total_aulas');
            $table->unsignedSmallInteger('total_capacidad');
            $table->enum('estado', ['borrador', 'confirmada'])->default('borrador');
            $table->timestamps();
        });

        Schema::create('distribucion_ambiente_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_distribucion');
            $table->unsignedBigInteger('id_ambiente');
            $table->unsignedBigInteger('id_aula_gestion');
            $table->unsignedTinyInteger('orden_ambiente');
            $table->string('codigo_asignado', 20);
            $table->unsignedSmallInteger('capacidad');
            $table->unsignedSmallInteger('estudiantes_asignados');
            $table->timestamps();

            $table->foreign('id_distribucion')->references('id')->on('distribucion_ambientes')->onDelete('cascade');
            $table->foreign('id_ambiente')->references('id')->on('ambientes')->onDelete('cascade');
            $table->foreign('id_aula_gestion')->references('id')->on('aulas_gestion')->onDelete('cascade');
            $table->index(['id_distribucion', 'orden_ambiente'], 'dist_amb_det_dist_orden_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribucion_ambiente_detalles');
        Schema::dropIfExists('distribucion_ambientes');
    }
};
