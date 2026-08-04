<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignacion_personal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_distribucion');
            $table->unsignedBigInteger('id_participante');
            $table->unsignedBigInteger('id_cargo');
            $table->unsignedBigInteger('id_ambiente')->nullable();
            $table->unsignedBigInteger('id_aula_gestion')->nullable();
            $table->unsignedBigInteger('id_distribucion_detalle')->nullable();
            $table->enum('turno', ['manana', 'tarde', 'noche'])->nullable();
            $table->enum('estado', ['asignado', 'confirmado', 'ausente', 'reasignado'])->default('asignado');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('id_distribucion')
                ->references('id')->on('distribucion_ambientes')
                ->onDelete('cascade');
            $table->foreign('id_participante')
                ->references('id')->on('participantes')
                ->onDelete('cascade');
            $table->foreign('id_cargo')
                ->references('id')->on('cargos')
                ->onDelete('cascade');
            $table->foreign('id_ambiente')
                ->references('id')->on('ambientes')
                ->onDelete('cascade');
            $table->foreign('id_aula_gestion')
                ->references('id')->on('aulas_gestion')
                ->onDelete('cascade');
            $table->foreign('id_distribucion_detalle')
                ->references('id')->on('distribucion_ambiente_detalles')
                ->onDelete('cascade');

            $table->index(['id_distribucion', 'id_cargo'], 'asig_personal_dist_cargo_idx');
            $table->index(['id_distribucion', 'id_aula_gestion'], 'asig_personal_dist_aula_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignacion_personal');
    }
};
