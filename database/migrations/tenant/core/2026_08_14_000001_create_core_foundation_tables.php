<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Core Installation
        |--------------------------------------------------------------------------
        */

        Schema::create('core_installations', function (Blueprint $table) {
            $table->id();

            $table->string('core_name')->default('core');
            $table->string('core_version')->default('1.0.0');

            $table->string('status')->default('installed');

            $table->timestamp('installed_at')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Website Settings
        |--------------------------------------------------------------------------
        */

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();

            $table->string('key')->unique();
            $table->longText('value')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Tenant Administrators
        |--------------------------------------------------------------------------
        */

        Schema::create('site_users', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();

            $table->string('password');

            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();

            $table->rememberToken();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Core Resource Usage
        |--------------------------------------------------------------------------
        */

        Schema::create('core_resource_usage', function (Blueprint $table) {
            $table->id();

            $table->string('resource');
            $table->decimal('used', 20, 4)->default(0);
            $table->decimal('limit', 20, 4)->nullable();

            $table->string('unit')->nullable();

            $table->timestamps();

            $table->unique('resource');
        });

        /*
        |--------------------------------------------------------------------------
        | Core Licenses
        |--------------------------------------------------------------------------
        */

        Schema::create('core_licenses', function (Blueprint $table) {
            $table->id();

            $table->string('license_key')->unique();

            $table->string('product_type')->default('core');
            $table->string('product_name')->default('core');
            $table->string('product_version')->default('1.0.0');

            $table->string('status')->default('active');

            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_licenses');
        Schema::dropIfExists('core_resource_usage');
        Schema::dropIfExists('site_users');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('core_installations');
    }
};
