<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Copiar valores de id_proceso_actual a id_proceso si id_proceso está vacío
        DB::table('users')
            ->whereNull('id_proceso')
            ->whereNotNull('id_proceso_actual')
            ->update(['id_proceso' => DB::raw('id_proceso_actual')]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_proceso_actual']);
            $table->dropColumn('id_proceso_actual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('id_proceso_actual')->nullable()->after('id_rol');
            $table->foreign('id_proceso_actual')->references('id')->on('procesos')->nullOnDelete();
        });
    }
};
