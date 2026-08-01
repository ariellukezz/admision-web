<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sorteo_seleccionados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_participante')->constrained('participantes_personales')->cascadeOnDelete();
            $table->foreignId('id_cargo')->nullable()->constrained('cargos')->nullOnDelete();
            $table->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sorteo_seleccionados');
    }
};
