<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_notices', function (Blueprint $table) {
            $table->id();

            $table->string('title', 160);
            $table->text('message');

            /*
             * draft     = saved but invisible to tenants
             * published = eligible for tenant display
             * disabled  = manually withdrawn
             */
            $table->string('status', 20)->default('draft')->index();

            /*
             * all      = every eligible tenant
             * selected = only tenant IDs stored in tenant_ids
             */
            $table->string('target_type', 20)->default('all');
            $table->json('tenant_ids')->nullable();

            /*
             * Optional scheduling.
             *
             * NULL published_at means immediately eligible once published.
             * NULL expires_at means remain active until disabled.
             */
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();

            /*
             * Central Admin user that created the notice.
             * Kept nullable to avoid coupling this central feature to a
             * specific authentication schema.
             */
            $table->unsignedBigInteger('created_by')->nullable()->index();

            $table->timestamps();

            $table->index(
                ['status', 'target_type'],
                'dashboard_notices_status_target_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_notices');
    }
};
