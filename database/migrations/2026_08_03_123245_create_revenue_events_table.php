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
        Schema::create('revenue_events', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Ownership
            |--------------------------------------------------------------------------
            */

            $table->foreignId('workspace_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            /*
            |--------------------------------------------------------------------------
            | Source
            |--------------------------------------------------------------------------
            |
            | The business module that generated the revenue.
            |
            */

            $table->string('source_module');

            /*
            |--------------------------------------------------------------------------
            | Event
            |--------------------------------------------------------------------------
            */

            $table->string('event_type');

            /*
            |--------------------------------------------------------------------------
            | Reference
            |--------------------------------------------------------------------------
            |
            | Polymorphic reference to the originating record.
            |
            */

            $table->nullableMorphs('reference');

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Revenue
            |--------------------------------------------------------------------------
            */

            $table->decimal('gross_amount', 15, 2);

            $table->decimal('discount_amount', 15, 2)->default(0);

            $table->decimal('tax_amount', 15, 2)->default(0);

            $table->decimal('net_amount', 15, 2);

            $table->string('currency', 3)->default('NGN');

            /*
            |--------------------------------------------------------------------------
            | Processing
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_commissionable')->default(true);

            $table->boolean('is_processed')->default(false);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'pending',

                'processed',

                'cancelled',

                'reversed'

            ])->default('pending');

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
        Schema::dropIfExists('revenue_events');
    }
};
