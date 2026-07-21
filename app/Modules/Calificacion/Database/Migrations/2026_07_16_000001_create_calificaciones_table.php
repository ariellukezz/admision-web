<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_proceso');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('id_multiplicador')->nullable();
            $table->unsignedBigInteger('id_ponderacion')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();

            $table->foreign('id_proceso')->references('id')->on('procesos');
            $table->foreign('id_multiplicador')->references('id')->on('multiplicadores')->onDelete('set null');
            $table->foreign('id_ponderacion')->references('id')->on('ponderacion_simulacro')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};
