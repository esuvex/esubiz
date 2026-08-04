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
        Schema::create('api_usages', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('api_key_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('api_application_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('workspace_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Usage Period
            |--------------------------------------------------------------------------
            */

            $table->date('date');

            /*
            |--------------------------------------------------------------------------
            | Counters
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('requests')->default(0);

            $table->unsignedBigInteger('successful_requests')->default(0);

            $table->unsignedBigInteger('failed_requests')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Resources
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('tokens_used')->default(0);

            $table->unsignedBigInteger('bandwidth_used')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Billing
            |--------------------------------------------------------------------------
            */

            $table->decimal('cost', 18, 8)->default(0);

            $table->string('currency', 3)->default('NGN');

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->unique([
                'api_key_id',
                'date'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_usages');
    }
};
