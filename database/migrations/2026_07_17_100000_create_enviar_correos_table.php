<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enviar_correos', function (Blueprint $table) {
            $table->id();
            $table->string('nombres');
            $table->string('correo');
            $table->string('programa')->nullable();
            $table->string('puerta')->nullable();
            $table->string('area');
            $table->boolean('enviado')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enviar_correos');
    }
};
