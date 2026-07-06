<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('multiplicadores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->decimal('correcta', 6, 3)->default(10);
            $table->decimal('incorrecta', 6, 3)->default(0);
            $table->decimal('blanco', 6, 3)->default(2);
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        DB::table('multiplicadores')->insert([
            'nombre' => 'Estándar CEPREUNA',
            'correcta' => 10,
            'incorrecta' => 0,
            'blanco' => 2,
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('multiplicadores');
    }
};
