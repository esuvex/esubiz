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
        Schema::table('websites', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Wizard
            |--------------------------------------------------------------------------
            */

            $table->unsignedTinyInteger('current_step')
                ->default(1)
                ->after('status');

            $table->json('wizard_data')
                ->nullable()
                ->after('current_step');

            $table->timestamp('last_saved_at')
                ->nullable()
                ->after('wizard_data');

            /*
            |--------------------------------------------------------------------------
            | Website Administrator
            |--------------------------------------------------------------------------
            */

            $table->string('admin_name')
                ->nullable()
                ->after('last_saved_at');

            $table->string('admin_email')
                ->nullable()
                ->after('admin_name');

            $table->string('admin_password')
                ->nullable()
                ->after('admin_email');

            /*
            |--------------------------------------------------------------------------
            | Developer / Reseller
            |--------------------------------------------------------------------------
            */

            $table->foreignId('developer_id')
                ->nullable()
                ->after('owner_id')
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Deployment
            |--------------------------------------------------------------------------
            */

            $table->unsignedTinyInteger('deployment_progress')
                ->default(0)
                ->after('admin_password');

            $table->timestamp('estimated_finish_at')
                ->nullable()
                ->after('deployment_progress');

            $table->timestamp('deployment_started_at')
                ->nullable()
                ->after('estimated_finish_at');

            $table->timestamp('deployment_completed_at')
                ->nullable()
                ->after('deployment_started_at');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {

            $table->dropConstrainedForeignId('developer_id');

            $table->dropColumn([
                'current_step',
                'wizard_data',
                'last_saved_at',

                'admin_name',
                'admin_email',
                'admin_password',

                'deployment_progress',
                'estimated_finish_at',
                'deployment_started_at',
                'deployment_completed_at',
            ]);

        });
    }
};
