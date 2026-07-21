<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aulas_gestion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pabellon');
            $table->string('codigo', 20);
            $table->unsignedTinyInteger('piso')->default(1);
            $table->unsignedSmallInteger('capacidad')->default(40);
            $table->enum('tipo', ['aula', 'laboratorio', 'auditorio', 'otro'])->default('aula');
            $table->boolean('estado')->default(true);
            $table->timestamps();

            $table->foreign('id_pabellon')->references('id')->on('pabellones')->onDelete('cascade');
            $table->unique(['codigo', 'id_pabellon']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aulas_gestion');
    }
};
