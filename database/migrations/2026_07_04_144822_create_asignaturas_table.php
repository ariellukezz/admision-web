<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaturas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('orden')->default(0);
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        $asignaturas = [
            'Aritmética', 'Álgebra', 'Geometría', 'Trigonometría',
            'Física', 'Química', 'Biología y Anatomía', 'Psicología y Filosofía',
            'Geografía', 'Historia', 'Educación Cívica', 'Economía',
            'Comunicación', 'Literatura', 'Razonamiento Matemático',
            'Razonamiento Verbal', 'Inglés', 'Quechua y aimara',
        ];

        foreach ($asignaturas as $i => $nombre) {
            DB::table('asignaturas')->insert([
                'nombre' => $nombre,
                'orden' => $i + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaturas');
    }
};
