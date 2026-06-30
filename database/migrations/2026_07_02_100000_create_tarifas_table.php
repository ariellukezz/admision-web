<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifas', function (Blueprint $table) {
            $table->id();
            $table->string('concepto', 255);
            $table->decimal('monto', 10, 2);
            $table->string('moneda', 5)->default('PEN');
            $table->foreignId('id_proceso')->nullable()->constrained('procesos')->nullOnDelete();
            $table->foreignId('id_programa')->nullable()->constrained('programa')->nullOnDelete();
            $table->foreignId('id_modalidad')->nullable()->constrained('modalidad')->nullOnDelete();
            $table->boolean('estado')->default(true);
            $table->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifas');
    }
};
