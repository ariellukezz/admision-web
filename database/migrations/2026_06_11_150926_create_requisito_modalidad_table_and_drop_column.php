<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisito_modalidad', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_requisito_documento');
            $table->unsignedBigInteger('id_modalidad');
            $table->timestamps();

            $table->foreign('id_requisito_documento')->references('id')->on('requisito_documento')->onDelete('cascade');
            $table->foreign('id_modalidad')->references('id')->on('modalidad')->onDelete('cascade');
        });

        // Migrate existing data
        $existing = DB::table('requisito_documento')->whereNotNull('id_modalidad_proceso')->get();
        foreach ($existing as $row) {
            DB::table('requisito_modalidad')->insert([
                'id_requisito_documento' => $row->id,
                'id_modalidad' => $row->id_modalidad_proceso,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Drop FK and column
        Schema::table('requisito_documento', function (Blueprint $table) {
            $table->dropForeign('requisito_documento_id_modalidad_proceso_foreign');
            $table->dropColumn('id_modalidad_proceso');
        });
    }

    public function down(): void
    {
        Schema::table('requisito_documento', function (Blueprint $table) {
            $table->unsignedBigInteger('id_modalidad_proceso')->nullable()->after('nombre');
            $table->foreign('id_modalidad_proceso')->references('id')->on('modalidad')->onDelete('cascade');
        });

        $pivotData = DB::table('requisito_modalidad')->get();
        foreach ($pivotData as $row) {
            DB::table('requisito_documento')
                ->where('id', $row->id_requisito_documento)
                ->update(['id_modalidad_proceso' => $row->id_modalidad]);
        }

        Schema::dropIfExists('requisito_modalidad');
    }
};
