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
        Schema::create('workspace_members', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('workspace_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Membership
            |--------------------------------------------------------------------------
            */

            $table->enum('member_type', [

                'owner',

                'administrator',

                'staff',

                'developer',

                'client',

                'guest'

            ])->default('staff');

            /*
            |--------------------------------------------------------------------------
            | Invitation
            |--------------------------------------------------------------------------
            */

            $table->string('invite_email')->nullable();

            $table->timestamp('joined_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'pending',

                'active',

                'suspended',

                'removed'

            ])->default('pending');

            /*
            |--------------------------------------------------------------------------
            | Access
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_primary')->default(false);

            $table->boolean('is_billable')->default(true);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Constraints
            |--------------------------------------------------------------------------
            */

            $table->unique([

                'workspace_id',

                'user_id'

            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_members');
    }
};
