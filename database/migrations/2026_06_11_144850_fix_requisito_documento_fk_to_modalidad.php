<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisito_documento', function (Blueprint $table) {
            $table->dropForeign('requisito_documento_id_modalidad_proceso_foreign');
        });

        Schema::table('requisito_documento', function (Blueprint $table) {
            $table->foreign('id_modalidad_proceso')->references('id')->on('modalidad')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('requisito_documento', function (Blueprint $table) {
            $table->dropForeign('requisito_documento_id_modalidad_proceso_foreign');
        });

        Schema::table('requisito_documento', function (Blueprint $table) {
            $table->foreign('id_modalidad_proceso')->references('id')->on('modalidad_proceso')->onDelete('cascade');
        });
    }
};
