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
        Schema::create('referral_invitations', function (Blueprint $table) {

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
            | Referrer
            |--------------------------------------------------------------------------
            */

            $table->foreignId('referral_member_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Invitation
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->string('code');

            $table->string('channel');

            $table->string('recipient')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Conversion
            |--------------------------------------------------------------------------
            */

            $table->foreignId('referred_member_id')
                ->nullable()
                ->constrained('referral_members')
                ->nullOnDelete();

            $table->timestamp('accepted_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'pending',

                'accepted',

                'expired',

                'cancelled'

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
        Schema::dropIfExists('referral_invitations');
    }
};
