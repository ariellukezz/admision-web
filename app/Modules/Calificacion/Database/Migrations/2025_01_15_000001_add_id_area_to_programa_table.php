<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programa', function (Blueprint $table) {
            $table->unsignedBigInteger('id_area')->nullable()->after('area');

            $table->foreign('id_area')
                ->references('id')
                ->on('areas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('programa', function (Blueprint $table) {
            $table->dropForeign(['id_area']);
            $table->dropColumn('id_area');
        });
    }
};
