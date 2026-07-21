<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prueba_tipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_prueba')->constrained('pruebas')->cascadeOnDelete();
            $table->foreignId('id_archivo')->constrained('prueba_archivos')->cascadeOnDelete();
            $table->string('tipo', 1)->comment('P/Q/R/S/T');
            $table->string('respuestas', 60);
            $table->timestamps();

            $table->unique(['id_prueba', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prueba_tipos');
    }
};
