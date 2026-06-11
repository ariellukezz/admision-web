<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documento', function (Blueprint $table) {
            $table->string('extension', 50)->nullable()->after('observacion');
            $table->string('mime', 255)->nullable()->after('extension');
            $table->bigInteger('size')->nullable()->after('mime');
            $table->string('path', 500)->nullable()->after('size');
            $table->string('hash', 64)->nullable()->after('path');
            $table->boolean('is_deleted')->default(false)->after('hash');
            $table->integer('version')->default(1)->after('is_deleted');

            $table->index('hash');
            $table->index('is_deleted');
        });
    }

    public function down(): void
    {
        Schema::table('documento', function (Blueprint $table) {
            $table->dropIndex(['hash']);
            $table->dropIndex(['is_deleted']);
            $table->dropColumn(['extension', 'mime', 'size', 'path', 'hash', 'is_deleted', 'version']);
        });
    }
};
