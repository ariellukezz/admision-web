<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participantes_personales', function (Blueprint $table) {
            $table->string('condicion', 100)->nullable()->after('codigo_trabajador');
            $table->string('dependencia', 255)->nullable()->after('condicion');
        });
    }

    public function down(): void
    {
        Schema::table('participantes_personales', function (Blueprint $table) {
            $table->dropColumn(['condicion', 'dependencia']);
        });
    }
};
