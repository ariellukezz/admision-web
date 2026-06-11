<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revisor_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('id_proceso');
            $table->string('alias');
            $table->timestamps();

            $table->unique(['user_id', 'id_proceso']);
            $table->index('id_proceso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revisor_aliases');
    }
};
