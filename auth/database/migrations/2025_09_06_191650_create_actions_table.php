<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('description');
        });

        DB::table('actions')->insert([
            ['code' => 'create', 'description' => 'Crear registros'],
            ['code' => 'read', 'description' => 'Ver registros'],
            ['code' => 'update', 'description' => 'Actualizar registros'],
            ['code' => 'delete', 'description' => 'Eliminar registros'],
            ['code' => 'export', 'description' => 'Exportar datos'],
            ['code' => 'print', 'description' => 'Imprimir datos'],
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actions');
    }
};
