<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vincular distribucion_ambientes con grupos_filtro
        Schema::table('distribucion_ambientes', function (Blueprint $table) {
            $table->unsignedBigInteger('id_grupo_filtro')->nullable()->after('id_proceso');
            $table->foreign('id_grupo_filtro')
                ->references('id')->on('grupos_filtro')
                ->onDelete('cascade');
        });

        // Vincular cada detalle con el aula virtual y su área
        Schema::table('distribucion_ambiente_detalles', function (Blueprint $table) {
            $table->unsignedBigInteger('id_aula')->nullable()->after('id_aula_gestion');
            $table->string('area_nombre', 100)->nullable()->after('id_aula');
            $table->string('aula_virtual_nombre', 100)->nullable()->after('area_nombre');
            $table->foreign('id_aula')
                ->references('id')->on('aulas')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('distribucion_ambiente_detalles', function (Blueprint $table) {
            $table->dropForeign(['id_aula']);
            $table->dropColumn(['id_aula', 'area_nombre', 'aula_virtual_nombre']);
        });

        Schema::table('distribucion_ambientes', function (Blueprint $table) {
            $table->dropForeign(['id_grupo_filtro']);
            $table->dropColumn('id_grupo_filtro');
        });
    }
};
