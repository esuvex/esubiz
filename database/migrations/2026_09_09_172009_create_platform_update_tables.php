<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Central registry of deployable Esubiz updates.
         *
         * This table does not modify tenant data. It describes an
         * update package that the Central Admin update engine can
         * preview, approve, deploy and audit.
         */
        Schema::create('platform_updates', function (Blueprint $table) {
            $table->id();

            $table->string('product_type', 50)->default('core');
            $table->unsignedBigInteger('product_id')->nullable();

            $table->string('name', 150);
            $table->string('version', 50);
            $table->text('description')->nullable();

            $table->string('update_type', 30)->default('migration');
            $table->string('status', 30)->default('draft');

            $table->string('migration_path', 255)->nullable();
            $table->string('package_path', 255)->nullable();

            $table->json('manifest')->nullable();
            $table->json('prerequisites')->nullable();

            $table->boolean('requires_backup')->default(true);
            $table->boolean('is_destructive')->default(false);

            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->unique(
                ['product_type', 'product_id', 'version'],
                'upd_prod_ver_uq'
            );

            $table->index('status', 'upd_status_idx');
            $table->index('product_type', 'upd_type_idx');
        });

        /*
         * One row per website + update execution.
         *
         * This gives Central Admin a durable audit trail and allows
         * failed/skipped tenants to be retried without rerunning an
         * update against tenants that already completed it.
         */
        Schema::create('platform_update_runs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('platform_update_id');
            $table->unsignedBigInteger('website_id');

            $table->string('status', 30)->default('pending');
            $table->string('from_version', 50)->nullable();
            $table->string('to_version', 50)->nullable();

            $table->boolean('dry_run')->default(false);
            $table->string('checkpoint', 255)->nullable();

            $table->text('message')->nullable();
            $table->json('details')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->unsignedBigInteger('executed_by')->nullable();

            $table->timestamps();

            $table->unique(
                ['platform_update_id', 'website_id'],
                'upd_run_site_uq'
            );

            $table->index('website_id', 'upd_site_idx');
            $table->index('status', 'upd_run_stat_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_update_runs');
        Schema::dropIfExists('platform_updates');
    }
};
