<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tabla para guardar selecciones de columnas
        Schema::create('selecciones_columnas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('nombre_tabla', 100);
            $table->json('columnas_seleccionadas');
            $table->string('hash_columnas', 64)->nullable();
            $table->timestamps();
            
            $table->index('nombre_tabla');
            $table->index('hash_columnas');
        });

        // Tabla para grupos de filtrado
        Schema::create('grupos_filtro', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seleccion_id');
            $table->string('descripcion', 255)->nullable();
            
            // Contexto obligatorio: INT SIGNED
            $table->integer('id_modalidad');
            $table->integer('id_proceso');
            
            // Tipo de filtro: 'general', 'area', 'programa'
            $table->enum('nivel_filtro', ['general', 'area', 'programa'])->default('general');
            
            // Valores del filtro (JSON para múltiples valores)
            $table->json('valor_nivel_filtro')->nullable();
            
            // Orden de procesamiento
            $table->unsignedInteger('orden_procesamiento')->default(999);
            
            // Contador de postulantes
            $table->unsignedInteger('postulantes_count')->default(0);
            
            $table->timestamps();
            
            // Índices
            $table->index(['id_modalidad', 'id_proceso']);
            $table->index('orden_procesamiento');
            
            // Foreign keys
            $table->foreign('seleccion_id')
                ->references('id')
                ->on('selecciones_columnas')
                ->onDelete('cascade');
        });

        // Tabla para condiciones de filtrado
        Schema::create('condiciones_filtro', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grupo_filtro_id');
            $table->string('nombre_columna', 255);
            $table->enum('tipo_condicion', ['last_n', 'first_n', 'nth_digit', 'nth_letter', 'middle_m_n', 'reverse_n_m']);
            $table->json('parametros_condicion');
            $table->timestamps();
            
            $table->foreign('grupo_filtro_id')
                ->references('id')
                ->on('grupos_filtro')
                ->onDelete('cascade');
        });

        // Tabla para códigos generados
        Schema::create('codigos_postulantes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grupo_filtro_id');
            $table->integer('id_postulante'); // INT SIGNED
            $table->integer('id_modalidad'); // INT SIGNED
            $table->integer('id_proceso');   // INT SIGNED
            $table->string('codigo', 255);
            $table->timestamps();
            
            // Índice único para evitar duplicados
            $table->unique(['id_postulante', 'id_modalidad', 'id_proceso'], 'unique_postulante_proceso');
            
            // Índice para exclusión rápida
            $table->index(['id_modalidad', 'id_proceso', 'id_postulante'], 'idx_exclusion');
            
            // Foreign keys
            if (Schema::hasTable('grupos_filtro')) {
                $table->foreign('grupo_filtro_id')
                    ->references('id')
                    ->on('grupos_filtro')
                    ->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('codigos_postulantes');
        Schema::dropIfExists('condiciones_filtro');
        Schema::dropIfExists('grupos_filtro');
        Schema::dropIfExists('selecciones_columnas');
    }
};