<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smtp_accounts', function (Blueprint $table) {
            $table->text('error_message')->nullable()->after('is_default');
            $table->timestamp('error_at')->nullable()->after('error_message');
        });
    }

    public function down(): void
    {
        Schema::table('smtp_accounts', function (Blueprint $table) {
            $table->dropColumn(['error_message', 'error_at']);
        });
    }
};
