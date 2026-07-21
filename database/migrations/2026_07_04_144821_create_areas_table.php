<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo')->nullable();
            $table->integer('numero_base')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        // Seed initial areas
        DB::table('areas')->insert([
            ['nombre' => 'Biomédicas', 'codigo' => 'BIO', 'numero_base' => 101, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Ingenierías', 'codigo' => 'ING', 'numero_base' => 201, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Sociales', 'codigo' => 'SOC', 'numero_base' => 301, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
