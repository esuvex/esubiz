<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('developer_builds', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('developer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('project_name');
            $table->string('build_id')->unique();
            $table->string('version')->default('1.0.0');

            $table->string('website_type')->nullable();
            $table->string('capacity_bundle')->nullable();
            $table->string('theme')->nullable();

            $table->json('modules')->nullable();
            $table->json('configuration')->nullable();

            $table->enum('status', [
                'queued',
                'building',
                'success',
                'failed',
                'cancelled',
            ])->default('queued');

            $table->unsignedInteger('files_count')->default(0);
            $table->unsignedBigInteger('package_size')->default(0);

            $table->string('package_reference')->nullable();
            $table->string('download_url')->nullable();

            $table->text('error_message')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'developer_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_builds');
    }
};
