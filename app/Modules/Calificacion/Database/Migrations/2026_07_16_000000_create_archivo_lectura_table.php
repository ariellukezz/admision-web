<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archivo_lectura', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_examen');
            $table->string('nombre');
            $table->string('tipo', 10)->comment('ide o res');
            $table->string('area', 50)->nullable();
            $table->string('url', 150);
            $table->integer('estado')->default(1);
            $table->timestamps();

            $table->foreign('id_examen')
                ->references('id')
                ->on('examen_simulacro')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archivo_lectura');
    }
};