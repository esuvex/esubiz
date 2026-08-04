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
        Schema::create('workspace_capacities', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('workspace_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('capacity_type_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Capacity Allocation
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('allocated')->default(0);

            $table->unsignedBigInteger('used')->default(0);

            $table->unsignedBigInteger('reserved')->default(0);

            $table->boolean('is_unlimited')->default(false);

            /*
            |--------------------------------------------------------------------------
            | Expiry
            |--------------------------------------------------------------------------
            */

            $table->timestamp('expires_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')->default(true);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Constraints
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'workspace_id',
                'capacity_type_id'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_capacities');
    }
};
