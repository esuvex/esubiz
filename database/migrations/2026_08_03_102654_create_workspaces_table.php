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
        Schema::create('workspaces', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Ownership
            |--------------------------------------------------------------------------
            */

            $table->foreignId('owner_id')
                ->constrained('users')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->string('name');

            $table->string('slug')->unique();

            /*
            |--------------------------------------------------------------------------
            | Business Information
            |--------------------------------------------------------------------------
            */

            $table->string('business_type')->nullable();

            $table->string('country', 100)->nullable();

            $table->string('state', 100)->nullable();

            $table->string('city', 100)->nullable();

            $table->string('currency', 10)->default('NGN');

            $table->string('timezone')->default('Africa/Lagos');

            $table->string('language', 10)->default('en');

            /*
            |--------------------------------------------------------------------------
            | Workspace Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'draft',
                'trial',
                'active',
                'suspended',
                'archived'
            ])->default('trial');

            /*
            |--------------------------------------------------------------------------
            | Workspace Type
            |--------------------------------------------------------------------------
            */

            $table->boolean('developer_managed')->default(false);

            $table->boolean('is_demo')->default(false);

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            $table->timestamp('trial_ends_at')->nullable();

            $table->timestamp('published_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Timestamps
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
        Schema::dropIfExists('workspaces');
    }
};
