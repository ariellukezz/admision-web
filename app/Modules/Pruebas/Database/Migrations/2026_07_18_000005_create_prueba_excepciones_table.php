<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prueba_excepciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_prueba_tipo')->constrained('prueba_tipos')->cascadeOnDelete();
            $table->integer('nro_pregunta')->comment('1-60');
            $table->string('accion', 20)->comment('todas_validas, multiples_validas, anulada, asignar_puntaje');
            $table->string('claves_validas', 50)->nullable()->comment('Para multiples_validas, separadas por coma');
            $table->decimal('puntaje', 8, 3)->default(0)->comment('Para todas_validas y asignar_puntaje');
            $table->string('observacion', 200)->nullable();
            $table->string('tipo', 1)->nullable()->comment('P/Q/R/S/T');
            $table->timestamps();

            $table->unique(['id_prueba_tipo', 'nro_pregunta']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prueba_excepciones');
    }
};
