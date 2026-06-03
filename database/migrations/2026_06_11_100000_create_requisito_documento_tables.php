<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisito_documento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_modalidad_proceso');
            $table->unsignedBigInteger('id_tipo_documento');
            $table->boolean('obligatorio')->default(true);
            $table->integer('orden')->default(0);
            $table->boolean('estado')->default(true);
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->timestamps();

            $table->foreign('id_modalidad_proceso')->references('id')->on('modalidad_proceso')->onDelete('cascade');
            $table->foreign('id_tipo_documento')->references('id')->on('tipo_documento')->onDelete('cascade');
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('proceso_requisito', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_proceso');
            $table->unsignedBigInteger('id_requisito_documento');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('id_proceso')->references('id')->on('procesos')->onDelete('cascade');
            $table->foreign('id_requisito_documento')->references('id')->on('requisito_documento')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proceso_requisito');
        Schema::dropIfExists('requisito_documento');
    }
};
