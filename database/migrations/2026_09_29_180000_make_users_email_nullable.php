<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El ingreso con DNIe no aporta correo: la columna debe aceptar nulos.
     * Los correos existentes no se tocan y el índice único se conserva
     * (MySQL permite varios NULL en una columna UNIQUE).
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `users` MODIFY `email` VARCHAR(255) NULL');
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `users` MODIFY `email` VARCHAR(255) NOT NULL');
    }
};
