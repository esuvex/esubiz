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
        Schema::create('domains', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('website_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Domain Information
            |--------------------------------------------------------------------------
            */

            $table->string('domain')->unique();

            $table->enum('type', [

                'subdomain',

                'custom'

            ])->default('subdomain');

            /*
            |--------------------------------------------------------------------------
            | Ownership
            |--------------------------------------------------------------------------
            */

            $table->enum('ownership', [

                'esubiz',

                'external'

            ])->default('external');

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'pending',

                'verifying',

                'active',

                'expired',

                'suspended'

            ])->default('pending');

            /*
            |--------------------------------------------------------------------------
            | Features
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_primary')->default(false);

            $table->boolean('ssl_enabled')->default(false);

            $table->boolean('verified')->default(false);

            $table->boolean('email_enabled')->default(false);

            /*
            |--------------------------------------------------------------------------
            | DNS
            |--------------------------------------------------------------------------
            */

            $table->timestamp('verified_at')->nullable();

            $table->timestamp('ssl_installed_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Renewal
            |--------------------------------------------------------------------------
            */

            $table->date('expires_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
