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
        Schema::table('api_authorizations', function (Blueprint $table) {
            $table->string('access_token_hash')->nullable()->unique()->after('authorization_code');
            $table->timestamp('access_token_expires_at')->nullable()->after('expires_at');
            $table->timestamp('code_consumed_at')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_authorizations', function (Blueprint $table) {
            $table->dropUnique(['access_token_hash']);
            $table->dropColumn([
                'access_token_hash',
                'access_token_expires_at',
                'code_consumed_at',
            ]);
        });
    }
};
