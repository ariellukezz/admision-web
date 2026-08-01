<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participantes_personales', function (Blueprint $table) {
            $table->id();
            $table->string('dni', 12);
            $table->string('nombres', 255);
            $table->string('paterno', 255)->nullable();
            $table->string('materno', 255)->nullable();
            $table->foreignId('id_tipo_personal')->nullable()->constrained('tipo_personal')->nullOnDelete();
            $table->string('codigo_trabajador', 50)->nullable();
            $table->string('foto')->nullable();
            $table->boolean('estado')->default(true);
            $table->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participantes_personales');
    }
};
