<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sorteos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();
            $table->foreignId('id_proceso')->constrained('procesos')->cascadeOnDelete();
            $table->boolean('estado')->default(true);
            $table->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sorteo_tipo_personal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_sorteo')->constrained('sorteos')->cascadeOnDelete();
            $table->foreignId('id_tipo_personal')->constrained('tipo_personal')->cascadeOnDelete();
        });

        Schema::table('sorteo_seleccionados', function (Blueprint $table) {
            $table->foreignId('id_sorteo')->after('id')->constrained('sorteos')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sorteo_seleccionados', function (Blueprint $table) {
            $table->dropForeign(['id_sorteo']);
            $table->dropColumn('id_sorteo');
        });
        Schema::dropIfExists('sorteo_tipo_personal');
        Schema::dropIfExists('sorteos');
    }
};
