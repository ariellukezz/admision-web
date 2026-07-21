<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examen_simulacro', function (Blueprint $table) {
            $table->unsignedBigInteger('id_simulacro')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('examen_simulacro', function (Blueprint $table) {
            $table->unsignedBigInteger('id_simulacro')->nullable(false)->change();
        });
    }
};
