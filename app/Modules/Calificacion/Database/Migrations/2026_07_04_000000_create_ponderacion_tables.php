<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ponderacion_simulacro')) {
            Schema::create('ponderacion_simulacro', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->integer('total_preguntas')->default(60);
                $table->decimal('total_ponderacion', 10, 3)->default(3000);
                $table->boolean('estado')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ponderacion')) {
            Schema::create('ponderacion', function (Blueprint $table) {
                $table->id();
                $table->string('asignatura')->nullable();
                $table->integer('numero')->default(1);
                $table->decimal('ponderacion', 8, 3)->default(0);
                $table->unsignedBigInteger('id_ponderacion_simulacro');
                $table->foreign('id_ponderacion_simulacro')->references('id')->on('ponderacion_simulacro')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ponderacion');
        Schema::dropIfExists('ponderacion_simulacro');
    }
};
