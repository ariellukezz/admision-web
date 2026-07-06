<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add id_examen_tipo to excepciones
        if (!Schema::hasColumn('excepciones', 'id_examen_tipo')) {
            Schema::table('excepciones', function (Blueprint $table) {
                $table->unsignedBigInteger('id_examen_tipo')->nullable()->after('id');
                $table->foreign('id_examen_tipo')->references('id')->on('examen_tipos')->onDelete('cascade');
            });
        }

        // Add id_examen_tipo to res
        if (!Schema::hasColumn('res', 'id_examen_tipo')) {
            Schema::table('res', function (Blueprint $table) {
                $table->unsignedBigInteger('id_examen_tipo')->nullable()->after('id_archivo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('excepciones', 'id_examen_tipo')) {
            Schema::table('excepciones', function (Blueprint $table) {
                $table->dropForeign(['id_examen_tipo']);
                $table->dropColumn('id_examen_tipo');
            });
        }

        if (Schema::hasColumn('res', 'id_examen_tipo')) {
            Schema::table('res', function (Blueprint $table) {
                $table->dropColumn('id_examen_tipo');
            });
        }
    }
};
