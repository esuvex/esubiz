<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dashboard_notices', function (Blueprint $table) {
            $table->json('delivery_channels')
                ->nullable()
                ->after('delivery_scope');

            $table->string('central_target_type', 20)
                ->default('all')
                ->after('target_type');

            $table->json('central_role_ids')
                ->nullable()
                ->after('central_target_type');
        });

        /*
         * ESUBIZ_DASHBOARD_NOTICE_CENTRAL_ROLE_FOUNDATION_V15
         *
         * Preserve all existing Core delivery behaviour while moving
         * toward independent multi-channel notice delivery.
         */
        DB::table('dashboard_notices')
            ->orderBy('id')
            ->get([
                'id',
                'delivery_scope',
            ])
            ->each(function ($notice) {
                $scope = (string) ($notice->delivery_scope ?? 'all');

                $channels = match ($scope) {
                    'saas' => ['saas'],
                    'off_server' => ['off_server'],
                    default => ['saas', 'off_server'],
                };

                DB::table('dashboard_notices')
                    ->where('id', $notice->id)
                    ->update([
                        'delivery_channels' =>
                            json_encode(
                                $channels,
                                JSON_UNESCAPED_SLASHES
                            ),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('dashboard_notices', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_channels',
                'central_target_type',
                'central_role_ids',
            ]);
        });
    }
};
