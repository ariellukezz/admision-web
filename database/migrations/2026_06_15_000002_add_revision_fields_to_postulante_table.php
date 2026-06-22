<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('postulante', function (Blueprint $table) {
            $table->boolean('revision_solicitada')->default(false)->after('revisado');
            $table->timestamp('revision_solicitada_at')->nullable()->after('revision_solicitada');
            $table->unsignedInteger('veces_revision_solicitada')->default(0)->after('revision_solicitada_at');
        });
    }

    public function down(): void
    {
        Schema::table('postulante', function (Blueprint $table) {
            $table->dropColumn(['revision_solicitada', 'revision_solicitada_at', 'veces_revision_solicitada']);
        });
    }
};
