<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('postulante', function (Blueprint $table) {
            $table->boolean('revision_apto')->default(false)->after('revision_revisor_id');
            $table->timestamp('revision_apto_at')->nullable()->after('revision_apto');
        });
    }

    public function down(): void
    {
        Schema::table('postulante', function (Blueprint $table) {
            $table->dropColumn(['revision_apto', 'revision_apto_at']);
        });
    }
};
