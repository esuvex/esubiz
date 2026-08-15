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
        | AI Providers / Connections
        |--------------------------------------------------------------------------
        */

        Schema::create('ai_connections', function (Blueprint $table) {
            $table->id();

            $table->string('provider')->default('esubiz');

            $table->string('name');

            $table->string('status')->default('active');

            $table->json('settings')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | AI Usage
        |--------------------------------------------------------------------------
        */

        Schema::create('ai_usage', function (Blueprint $table) {
            $table->id();

            $table->string('feature');

            $table->string('model')->nullable();

            $table->unsignedBigInteger('credits_used')->default(0);

            $table->unsignedBigInteger('tokens_used')->default(0);

            $table->decimal('cost', 20, 6)->default(0);

            $table->string('status')->default('completed');

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('feature', 'ix_ai_usage_feature');
            $table->index('status', 'ix_ai_usage_status');
        });

        /*
        |--------------------------------------------------------------------------
        | AI Requests
        |--------------------------------------------------------------------------
        */

        Schema::create('ai_requests', function (Blueprint $table) {
            $table->id();

            $table->string('feature');

            $table->text('prompt')->nullable();

            $table->longText('response')->nullable();

            $table->string('status')->default('pending');

            $table->unsignedBigInteger('credits_used')->default(0);

            $table->timestamps();

            $table->index('feature', 'ix_ai_requests_feature');
            $table->index('status', 'ix_ai_requests_status');
        });

        /*
        |--------------------------------------------------------------------------
        | SMS Settings
        |--------------------------------------------------------------------------
        */

        Schema::create('sms_settings', function (Blueprint $table) {
            $table->id();

            $table->string('provider')->default('esubiz');

            $table->string('sender_name')->nullable();

            $table->string('status')->default('active');

            $table->json('settings')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | SMS Messages
        |--------------------------------------------------------------------------
        */

        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();

            $table->string('recipient');

            $table->text('message');

            $table->unsignedInteger('segments')->default(1);

            $table->unsignedInteger('credits_used')->default(0);

            $table->string('status')->default('pending');

            $table->string('provider_reference')->nullable();

            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->index('recipient', 'ix_sm_recipien');
            $table->index('status', 'ix_sm_status');
        });

        /*
        |--------------------------------------------------------------------------
        | Social Connections
        |--------------------------------------------------------------------------
        */

        Schema::create('social_connections', function (Blueprint $table) {
            $table->id();

            $table->string('platform');

            $table->string('name')->nullable();

            $table->string('account_id')->nullable();

            $table->string('account_name')->nullable();

            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();

            $table->timestamp('token_expires_at')->nullable();

            $table->string('status')->default('disconnected');

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique(
                ['platform', 'account_id'],
                'uq_soc_conn_platform_accoun'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Marketing Campaigns
        |--------------------------------------------------------------------------
        */

        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('type')->default('social');

            $table->string('status')->default('draft');

            $table->text('description')->nullable();

            $table->json('settings')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            $table->timestamps();

            $table->index('type', 'ix_mc_type');
            $table->index('status', 'ix_mc_status');
        });

        /*
        |--------------------------------------------------------------------------
        | Marketing Campaign Connections
        |--------------------------------------------------------------------------
        */

        Schema::create('marketing_campaign_connections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campaign_id')
                ->constrained('marketing_campaigns', 'id', 'fk_mcc_campaign')
                ->cascadeOnDelete();

            $table->foreignId('social_connection_id')
                ->constrained('social_connections', 'id', 'fk_mcc_social_c')
                ->cascadeOnDelete();

            $table->string('status')->default('active');

            $table->timestamps();

            $table->unique(
                ['campaign_id', 'social_connection_id'],
                'uq_mcc_campaign_social'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_campaign_connections');
        Schema::dropIfExists('marketing_campaigns');
        Schema::dropIfExists('social_connections');
        Schema::dropIfExists('sms_messages');
        Schema::dropIfExists('sms_settings');
        Schema::dropIfExists('ai_requests');
        Schema::dropIfExists('ai_usage');
        Schema::dropIfExists('ai_connections');
    }
};
