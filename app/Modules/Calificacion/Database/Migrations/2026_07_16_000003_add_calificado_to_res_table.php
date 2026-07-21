<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('res', function (Blueprint $table) {
            if (!Schema::hasColumn('res', 'calificado')) {
                $table->boolean('calificado')->default(0)->after('puntaje');
            }
        });
    }

    public function down(): void
    {
        Schema::table('res', function (Blueprint $table) {
            if (Schema::hasColumn('res', 'calificado')) {
                $table->dropColumn('calificado');
            }
        });
    }
};
