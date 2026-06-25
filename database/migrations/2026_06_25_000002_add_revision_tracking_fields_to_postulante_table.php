<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('postulante', function (Blueprint $table) {
            $table->timestamp('revision_iniciada_at')->nullable()->after('veces_revision_solicitada');
            $table->timestamp('revision_finalizada_at')->nullable()->after('revision_iniciada_at');
            $table->unsignedBigInteger('revision_revisor_id')->nullable()->after('revision_finalizada_at');
            $table->json('datos_citacion')->nullable()->after('revision_revisor_id');

            $table->foreign('revision_revisor_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('postulante', function (Blueprint $table) {
            $table->dropForeign(['revision_revisor_id']);
            $table->dropColumn(['revision_iniciada_at', 'revision_finalizada_at', 'revision_revisor_id', 'datos_citacion']);
        });
    }
};
