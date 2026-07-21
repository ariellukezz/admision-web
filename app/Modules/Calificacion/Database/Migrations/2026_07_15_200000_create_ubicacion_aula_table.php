<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubicacion_aula', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('pabellon', 100);
            $table->string('piso', 10);
            $table->unsignedSmallInteger('capacidad')->default(40);
            $table->string('area', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicacion_aula');
    }
};
