<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('examen_simulacro', 'id_ponderacion')) {
            // Try to drop foreign key if it exists, ignore errors
            try {
                Schema::table('examen_simulacro', function (Blueprint $table) {
                    $table->dropForeign(['id_ponderacion']);
                });
            } catch (\Exception $e) {
                // No foreign key, continue
            }

            Schema::table('examen_simulacro', function (Blueprint $table) {
                $table->dropColumn('id_ponderacion');
            });
        }
    }

    public function down(): void
    {
        Schema::table('examen_simulacro', function (Blueprint $table) {
            $table->unsignedBigInteger('id_ponderacion')->nullable()->after('n_alternativas');
        });
    }
};
