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
        Schema::create('api_limits', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('api_key_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Limit Type
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'requests',

                'bandwidth',

                'credits',

                'custom'

            ])->default('requests');

            /*
            |--------------------------------------------------------------------------
            | Limits
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('limit');

            $table->unsignedBigInteger('used')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Period
            |--------------------------------------------------------------------------
            */

            $table->enum('period', [

                'minute',

                'hour',

                'day',

                'month'

            ])->default('month');

            /*
            |--------------------------------------------------------------------------
            | Reset
            |--------------------------------------------------------------------------
            */

            $table->timestamp('reset_at')->nullable();

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

            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_limits');
    }
};
