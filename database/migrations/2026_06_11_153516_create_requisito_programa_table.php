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
        Schema::create('requisito_programa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_requisito_documento');
            $table->unsignedBigInteger('id_programa');
            $table->timestamps();

            $table->foreign('id_requisito_documento')->references('id')->on('requisito_documento')->onDelete('cascade');
            $table->foreign('id_programa')->references('id')->on('programa')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisito_programa');
    }
};
