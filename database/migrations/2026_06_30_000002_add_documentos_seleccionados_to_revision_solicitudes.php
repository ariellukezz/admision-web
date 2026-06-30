<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('revision_solicitudes', function (Blueprint $table) {
            $table->json('documentos_seleccionados')->nullable()->after('documentos_pendientes');
        });
    }

    public function down(): void
    {
        Schema::table('revision_solicitudes', function (Blueprint $table) {
            $table->dropColumn('documentos_seleccionados');
        });
    }
};
