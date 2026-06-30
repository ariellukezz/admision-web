<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documento', function (Blueprint $table) {
            $table->boolean('apto_revision')->default(false)->after('verificado');
            $table->boolean('valido')->default(false)->after('apto_revision');
            $table->date('fecha_caducidad')->nullable()->after('valido');
            $table->unsignedBigInteger('id_revisor')->nullable()->after('fecha_caducidad');
            $table->text('observacion_revisor')->nullable()->after('id_revisor');
            $table->timestamp('revisado_at')->nullable()->after('observacion_revisor');
            $table->timestamp('validado_at')->nullable()->after('revisado_at');
            $table->boolean('seleccionado')->default(false)->after('validado_at');

            $table->foreign('id_revisor')->references('id')->on('users')->onDelete('set null');
            $table->index('apto_revision');
            $table->index('valido');
            $table->index('id_revisor');
            $table->index('seleccionado');
        });
    }

    public function down(): void
    {
        Schema::table('documento', function (Blueprint $table) {
            $table->dropForeign(['id_revisor']);
            $table->dropIndex(['apto_revision']);
            $table->dropIndex(['valido']);
            $table->dropIndex(['id_revisor']);
            $table->dropIndex(['seleccionado']);
            $table->dropColumn([
                'apto_revision',
                'valido',
                'fecha_caducidad',
                'id_revisor',
                'observacion_revisor',
                'revisado_at',
                'validado_at',
                'seleccionado',
            ]);
        });
    }
};
