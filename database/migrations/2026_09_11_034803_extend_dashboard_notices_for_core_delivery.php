<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dashboard_notices', function (Blueprint $table) {
            /*
             * Which Core installations should receive the notice.
             *
             * all        = SaaS + off-server Core
             * saas       = Esubiz-hosted SaaS Core only
             * off_server = externally-hosted/off-server Core only
             */
            $table->string('delivery_scope', 20)
                ->default('all')
                ->after('target_type')
                ->index();

            /*
             * Core dashboard visual treatment.
             *
             * Supported values:
             * info, success, warning, danger, primary, secondary
             */
            $table->string('variant', 20)
                ->default('info')
                ->after('message');

            /*
             * Optional image displayed with the notice.
             * Can be an Esubiz media URL or another approved URL.
             */
            $table->text('image_url')
                ->nullable()
                ->after('variant');

            /*
             * Whether Core Admins may dismiss/close the notice.
             */
            $table->boolean('dismissible')
                ->default(true)
                ->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('dashboard_notices', function (Blueprint $table) {
            $table->dropIndex(['delivery_scope']);

            $table->dropColumn([
                'delivery_scope',
                'variant',
                'image_url',
                'dismissible',
            ]);
        });
    }
};
