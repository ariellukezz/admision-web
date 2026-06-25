<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revision_solicitudes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_postulante');
            $table->unsignedBigInteger('id_proceso');
            $table->unsignedBigInteger('id_modalidad')->nullable();
            $table->unsignedBigInteger('id_programa')->nullable();
            $table->unsignedInteger('veces')->default(1);
            $table->timestamp('solicitada_at');
            $table->timestamp('iniciada_at')->nullable();
            $table->timestamp('finalizada_at')->nullable();
            $table->unsignedBigInteger('revisor_id')->nullable();
            $table->boolean('apto')->default(false);
            $table->timestamp('apto_at')->nullable();
            $table->json('datos_citacion')->nullable();
            $table->json('documentos_verificados')->nullable();
            $table->json('documentos_pendientes')->nullable();
            $table->string('estado')->default('solicitada'); // solicitada, en_revision, completada
            $table->timestamps();

            $table->foreign('id_postulante')->references('id')->on('postulante')->onDelete('cascade');
            $table->foreign('revisor_id')->references('id')->on('users')->onDelete('set null');

            $table->index(['id_postulante', 'id_modalidad', 'id_proceso']);
            $table->index(['id_proceso', 'estado']);
            $table->index(['revisor_id', 'estado']);
            $table->index(['id_modalidad', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revision_solicitudes');
    }
};
