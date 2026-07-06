<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ponderacion', function (Blueprint $table) {
            if (!Schema::hasColumn('ponderacion', 'id_asignatura')) {
                $table->foreignId('id_asignatura')->nullable()->constrained('asignaturas')->nullOnDelete();
            }
            if (!Schema::hasColumn('ponderacion', 'cantidad_preguntas')) {
                $table->integer('cantidad_preguntas')->default(0);
            }
            if (!Schema::hasColumn('ponderacion', 'subtotal')) {
                $table->decimal('subtotal', 12, 3)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('ponderacion', function (Blueprint $table) {
            $table->dropForeign(['id_asignatura']);
            $table->dropColumn(['id_asignatura', 'cantidad_preguntas', 'subtotal']);
        });
    }
};
