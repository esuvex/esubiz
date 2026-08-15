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
        | Branches
        |--------------------------------------------------------------------------
        */

        Schema::create('branches', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code')->unique('uq_branches_code');

            $table->text('address')->nullable();

            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->json('settings')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Security Settings
        |--------------------------------------------------------------------------
        */

        Schema::create('security_settings', function (Blueprint $table) {
            $table->id();

            $table->boolean('two_factor_enabled')->default(false);

            $table->boolean('login_protection_enabled')->default(true);

            $table->unsignedInteger('max_login_attempts')->default(5);

            $table->unsignedInteger('lockout_minutes')->default(15);

            $table->boolean('ip_restriction_enabled')->default(false);

            $table->json('allowed_ips')->nullable();

            $table->json('settings')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Security Events
        |--------------------------------------------------------------------------
        */

        Schema::create('security_events', function (Blueprint $table) {
            $table->id();

            $table->string('event_type');

            $table->string('severity')->default('info');

            $table->string('ip_address')->nullable();

            $table->text('user_agent')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('event_type', 'ix_se_event_ty');
            $table->index('severity', 'ix_se_severity');
            $table->index('user_id', 'ix_se_user');
        });

        /*
        |--------------------------------------------------------------------------
        | QR Codes
        |--------------------------------------------------------------------------
        */

        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('code')->unique('uq_qc_code');

            $table->string('type')->default('url');

            $table->text('content');

            $table->string('file_path')->nullable();

            $table->unsignedInteger('scan_count')->default(0);

            $table->boolean('is_active')->default(true);

            $table->json('settings')->nullable();

            $table->timestamps();

            $table->index('type', 'ix_qc_type');
        });

        /*
        |--------------------------------------------------------------------------
        | 360 Degree Panorama
        |--------------------------------------------------------------------------
        */

        Schema::create('panoramas', function (Blueprint $table) {
            $table->id();

            $table->string('title');

            $table->text('description')->nullable();

            $table->string('image_path');

            $table->string('status')->default('draft');

            $table->boolean('is_active')->default(true);

            $table->json('settings')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Panorama Hotspots
        |--------------------------------------------------------------------------
        */

        Schema::create('panorama_hotspots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('panorama_id')
                ->constrained('panoramas', 'id', 'fk_ph_panorama')
                ->cascadeOnDelete();

            $table->string('title')->nullable();

            $table->text('description')->nullable();

            $table->decimal('position_x', 12, 6)->nullable();
            $table->decimal('position_y', 12, 6)->nullable();
            $table->decimal('position_z', 12, 6)->nullable();

            $table->string('target_url')->nullable();

            $table->json('settings')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panorama_hotspots');
        Schema::dropIfExists('panoramas');
        Schema::dropIfExists('qr_codes');
        Schema::dropIfExists('security_events');
        Schema::dropIfExists('security_settings');
        Schema::dropIfExists('branches');
    }
};
