<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prueba_ides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_prueba')->constrained('pruebas')->cascadeOnDelete();
            $table->foreignId('id_archivo')->constrained('prueba_archivos')->cascadeOnDelete();
            $table->string('camp1', 25)->nullable();
            $table->string('camp2', 10)->nullable();
            $table->string('camp3', 10)->nullable();
            $table->string('camp4', 5)->nullable();
            $table->string('litho', 6);
            $table->string('tipo', 1)->nullable()->comment('P/Q/R/S/T');
            $table->string('dni', 8);
            $table->string('aula', 5)->nullable();
            $table->integer('estado')->default(1);
            $table->timestamps();

            $table->index(['id_prueba', 'litho']);
            $table->index(['id_prueba', 'dni']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prueba_ides');
    }
};
