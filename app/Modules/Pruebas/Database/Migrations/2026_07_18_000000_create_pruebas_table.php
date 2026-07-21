<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pruebas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->foreignId('id_ponderacion')->nullable()->constrained('ponderacion_simulacro')->cascadeOnDelete();
            $table->foreignId('id_multiplicador')->nullable()->constrained('multiplicadores')->cascadeOnDelete();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pruebas');
    }
};
