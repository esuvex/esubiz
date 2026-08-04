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
        Schema::create('payment_gateways', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Owner
            |--------------------------------------------------------------------------
            */

            $table->foreignId('workspace_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->string('name');

            $table->string('slug');

            /*
            |--------------------------------------------------------------------------
            | Features
            |--------------------------------------------------------------------------
            */

            $table->boolean('supports_funding')->default(true);

            $table->boolean('supports_payout')->default(true);

            /*
            |--------------------------------------------------------------------------
            | Credentials
            |--------------------------------------------------------------------------
            */

            $table->json('credentials')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Environment
            |--------------------------------------------------------------------------
            */

            $table->enum('environment', [
                'sandbox',
                'production'
            ])->default('sandbox');

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_enabled')->default(false);

            $table->boolean('is_default')->default(false);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

            $table->unique([
                'workspace_id',
                'slug'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
