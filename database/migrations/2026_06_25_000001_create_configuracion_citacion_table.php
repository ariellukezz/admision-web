<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_citacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_proceso');
            $table->enum('tipo_criterio', ['mismo_dia', 'dni_digito', 'area', 'modalidad', 'programa']);
            $table->string('valor', 255)->nullable();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('lugar', 255);
            $table->text('instrucciones')->nullable();
            $table->boolean('estado')->default(true);
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->timestamps();

            $table->foreign('id_proceso')->references('id')->on('procesos')->onDelete('cascade');
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('set null');
            $table->index(['id_proceso', 'tipo_criterio', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_citacion');
    }
};
