<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('requisito_tipo_documento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_requisito_documento');
            $table->unsignedBigInteger('id_tipo_documento');
            $table->timestamps();

            $table->foreign('id_requisito_documento')->references('id')->on('requisito_documento')->onDelete('cascade');
            $table->foreign('id_tipo_documento')->references('id')->on('tipo_documento')->onDelete('cascade');
        });

        // Migrate existing data from requisito_documento.id_tipo_documento to pivot table
        $existing = DB::table('requisito_documento')->whereNotNull('id_tipo_documento')->get();
        foreach ($existing as $row) {
            DB::table('requisito_tipo_documento')->insert([
                'id_requisito_documento' => $row->id,
                'id_tipo_documento' => $row->id_tipo_documento,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Drop id_tipo_documento column from requisito_documento
        Schema::table('requisito_documento', function (Blueprint $table) {
            $table->dropForeign(['id_tipo_documento']);
            $table->dropColumn('id_tipo_documento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requisito_documento', function (Blueprint $table) {
            $table->unsignedBigInteger('id_tipo_documento')->nullable()->after('id_modalidad_proceso');
        });

        $pivotData = DB::table('requisito_tipo_documento')->get();
        foreach ($pivotData as $row) {
            DB::table('requisito_documento')
                ->where('id', $row->id_requisito_documento)
                ->update(['id_tipo_documento' => $row->id_tipo_documento]);
        }

        Schema::dropIfExists('requisito_tipo_documento');
    }
};
