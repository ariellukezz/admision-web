<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prueba_res', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_prueba')->constrained('pruebas')->cascadeOnDelete();
            $table->foreignId('id_archivo')->constrained('prueba_archivos')->cascadeOnDelete();
            $table->string('n_lectura', 6)->nullable();
            $table->string('c1', 5)->nullable();
            $table->string('c3', 5)->nullable();
            $table->string('litho', 6);
            $table->string('respuestas', 60);
            $table->decimal('puntaje', 8, 3)->nullable();
            $table->boolean('calificado')->default(false);
            $table->timestamps();

            $table->index(['id_prueba', 'litho']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prueba_res');
    }
};
