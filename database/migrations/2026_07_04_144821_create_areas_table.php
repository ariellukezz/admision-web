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
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        // Seed initial areas
        DB::table('areas')->insert([
            ['nombre' => 'Biomédicas', 'codigo' => 'BIO', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Ingenierías', 'codigo' => 'ING', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Sociales', 'codigo' => 'SOC', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
