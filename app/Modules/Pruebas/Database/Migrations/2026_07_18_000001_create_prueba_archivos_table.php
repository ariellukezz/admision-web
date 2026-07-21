<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prueba_archivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_prueba')->constrained('pruebas')->cascadeOnDelete();
            $table->string('nombre', 150);
            $table->string('tipo', 10)->comment('ide, res, tipos');
            $table->string('url', 200);
            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prueba_archivos');
    }
};
