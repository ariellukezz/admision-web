<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Módulos (ej: Postulantes, Procesos, Reportes, Configuración)
        Schema::create('rbac_modules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->string('name', 150);
            $table->string('description', 255)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Vistas dentro de cada módulo (ej: lista, crear, editar)
        Schema::create('rbac_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('rbac_modules')->cascadeOnDelete();
            $table->string('code', 100);
            $table->string('name', 150);
            $table->string('description', 255)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['module_id', 'code']);
        });

        // Acciones (create, read, update, delete, export, print, access)
        Schema::create('rbac_actions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('description', 150)->nullable();
        });

        // Permisos = View + Action (ej: postulantes.create, procesos.read)
        Schema::create('rbac_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('rbac_modules')->cascadeOnDelete();
            $table->foreignId('view_id')->constrained('rbac_views')->cascadeOnDelete();
            $table->foreignId('action_id')->constrained('rbac_actions')->cascadeOnDelete();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['view_id', 'action_id']);
        });

        // Pivot: Rol ↔ Permiso
        Schema::create('rbac_role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('rbac_permissions')->cascadeOnDelete();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->primary(['role_id', 'permission_id']);
        });

        // Pivot: Usuario ↔ Permiso (overrides: add o remove)
        Schema::create('rbac_user_permissions', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('rbac_permissions')->cascadeOnDelete();
            $table->enum('type', ['add', 'remove'])->default('add');
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->primary(['user_id', 'permission_id']);
        });

        // Seed default actions
        $actions = [
            ['code' => 'access', 'description' => 'Acceder al módulo'],
            ['code' => 'read', 'description' => 'Visualizar información'],
            ['code' => 'create', 'description' => 'Crear nuevos registros'],
            ['code' => 'update', 'description' => 'Modificar registros'],
            ['code' => 'delete', 'description' => 'Eliminar registros'],
            ['code' => 'export', 'description' => 'Exportar datos'],
            ['code' => 'print', 'description' => 'Generar PDF/imprimir'],
        ];
        foreach ($actions as $action) {
            DB::table('rbac_actions')->insert($action);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rbac_user_permissions');
        Schema::dropIfExists('rbac_role_permissions');
        Schema::dropIfExists('rbac_permissions');
        Schema::dropIfExists('rbac_actions');
        Schema::dropIfExists('rbac_views');
        Schema::dropIfExists('rbac_modules');
    }
};
