<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('postulante', function (Blueprint $table) {
            $table->string('nro_doc_opcional', 20)->nullable()->after('nro_doc');
            $table->string('tipo_discapacidad_otro', 255)->nullable()->after('tipo_discapacidad');
        });
    }

    public function down(): void
    {
        Schema::table('postulante', function (Blueprint $table) {
            $table->dropColumn(['nro_doc_opcional', 'tipo_discapacidad_otro']);
        });
    }
};
