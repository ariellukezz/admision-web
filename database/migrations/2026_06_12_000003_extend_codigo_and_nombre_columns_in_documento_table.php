<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documento', function (Blueprint $table) {
            $table->string('codigo', 50)->change();
            $table->string('nombre', 255)->change();
        });
    }

    public function down(): void
    {
        Schema::table('documento', function (Blueprint $table) {
            $table->string('codigo', 19)->change();
            $table->string('nombre', 40)->change();
        });
    }
};
