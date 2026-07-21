<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pabellon_programa', function (Blueprint $table) {
            $table->unsignedBigInteger('id_pabellon');
            $table->unsignedBigInteger('id_programa');
            $table->timestamps();

            $table->primary(['id_pabellon', 'id_programa']);
            $table->foreign('id_pabellon')->references('id')->on('pabellones')->onDelete('cascade');
            $table->foreign('id_programa')->references('id')->on('programa')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pabellon_programa');
    }
};
