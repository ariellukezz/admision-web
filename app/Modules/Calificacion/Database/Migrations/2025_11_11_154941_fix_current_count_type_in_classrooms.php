<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aulas', function (Blueprint $table) {
            // Cambiar de unsignedTinyInteger (max 255) a unsignedSmallInteger (max 65535)
            $table->unsignedSmallInteger('contador_actual')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('aulas', function (Blueprint $table) {
            $table->unsignedTinyInteger('contador_actual')->default(0)->change();
        });
    }
};