<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examen_tipos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_archivo')->nullable()->after('id_examen_simulacro');
            $table->foreign('id_archivo')->references('id')->on('archivos_simulacro')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('examen_tipos', function (Blueprint $table) {
            $table->dropForeign(['id_archivo']);
            $table->dropColumn('id_archivo');
        });
    }
};
