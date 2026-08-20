<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aulas_gestion', function (Blueprint $table) {
            $table->dropForeign('aulas_gestion_id_pabellon_foreign');
            $table->dropColumn('id_pabellon');
        });
    }

    public function down(): void
    {
        Schema::table('aulas_gestion', function (Blueprint $table) {
            $table->unsignedBigInteger('id_pabellon')->after('id');
            $table->foreign('id_pabellon')->references('id')->on('pabellones')->onDelete('cascade');
        });
    }
};
