<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('archivos_simulacro', 'id_examen_tipo')) {
            Schema::table('archivos_simulacro', function (Blueprint $table) {
                $table->unsignedBigInteger('id_examen_tipo')->nullable()->after('id_simulacro');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('archivos_simulacro', 'id_examen_tipo')) {
            Schema::table('archivos_simulacro', function (Blueprint $table) {
                $table->dropColumn('id_examen_tipo');
            });
        }
    }
};
