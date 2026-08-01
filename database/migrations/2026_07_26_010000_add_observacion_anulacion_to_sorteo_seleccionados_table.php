<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sorteo_seleccionados', function (Blueprint $table) {
            $table->text('observacion')->nullable()->after('id_usuario');
            $table->boolean('observado')->default(false)->after('observacion');
            $table->boolean('anulado')->default(false)->after('observado');
            $table->text('motivo_anulacion')->nullable()->after('anulado');
        });
    }

    public function down(): void
    {
        Schema::table('sorteo_seleccionados', function (Blueprint $table) {
            $table->dropColumn(['observacion', 'observado', 'anulado', 'motivo_anulacion']);
        });
    }
};
