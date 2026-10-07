<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('two_factor_enabled')->default(false)->after('estado');
            $table->string('two_factor_otp_hash')->nullable()->after('two_factor_enabled');
            $table->timestamp('two_factor_otp_expires_at')->nullable()->after('two_factor_otp_hash');
            $table->unsignedTinyInteger('two_factor_otp_attempts')->default(0)->after('two_factor_otp_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_enabled',
                'two_factor_otp_hash',
                'two_factor_otp_expires_at',
                'two_factor_otp_attempts',
            ]);
        });
    }
};
