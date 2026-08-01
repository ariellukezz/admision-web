<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sorteo_cargo_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_sorteo')->constrained('sorteos')->cascadeOnDelete();
            $table->foreignId('id_cargo')->constrained('cargos')->cascadeOnDelete();
            $table->unsignedInteger('cantidad')->default(0);
            $table->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['id_sorteo', 'id_cargo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sorteo_cargo_config');
    }
};
