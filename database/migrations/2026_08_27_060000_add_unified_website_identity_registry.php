<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'websites',
            function (Blueprint $table) {

                /*
                 * Permanent public-safe Central identity.
                 *
                 * Numeric id remains the internal relational key,
                 * while website_uuid can safely travel through APIs.
                 */
                if (
                    !Schema::hasColumn(
                        'websites',
                        'website_uuid'
                    )
                ) {
                    $table->uuid(
                        'website_uuid'
                    )
                        ->nullable()
                        ->after('id');
                }


                /*
                 * Explicit deployment classification.
                 *
                 * Central account is NOT a deployment type.
                 * It is a checkout origin.
                 */
                if (
                    !Schema::hasColumn(
                        'websites',
                        'deployment_type'
                    )
                ) {
                    $table->string(
                        'deployment_type',
                        32
                    )
                        ->nullable()
                        ->after('workspace_id');
                }


                /*
                 * Canonical current domain.
                 *
                 * SaaS may use a tenant/custom domain.
                 * Off-server will later be domain-locked through
                 * the licence/installation registry.
                 */
                if (
                    !Schema::hasColumn(
                        'websites',
                        'registered_domain'
                    )
                ) {
                    $table->string(
                        'registered_domain',
                        255
                    )
                        ->nullable()
                        ->after('deployment_type');
                }


                /*
                 * Central website lifecycle.
                 */
                if (
                    !Schema::hasColumn(
                        'websites',
                        'registry_status'
                    )
                ) {
                    $table->string(
                        'registry_status',
                        32
                    )
                        ->default('active')
                        ->after('registered_domain');
                }


                if (
                    !Schema::hasColumn(
                        'websites',
                        'registered_at'
                    )
                ) {
                    $table->timestamp(
                        'registered_at'
                    )
                        ->nullable()
                        ->after('registry_status');
                }


                if (
                    !Schema::hasColumn(
                        'websites',
                        'last_central_seen_at'
                    )
                ) {
                    $table->timestamp(
                        'last_central_seen_at'
                    )
                        ->nullable()
                        ->after('registered_at');
                }
            }
        );


        /*
         * ------------------------------------------------------------
         * BACKFILL PERMANENT UUIDs
         * ------------------------------------------------------------
         */
        DB::table('websites')
            ->whereNull('website_uuid')
            ->orderBy('id')
            ->get(['id'])
            ->each(
                function ($website) {
                    DB::table('websites')
                        ->where(
                            'id',
                            $website->id
                        )
                        ->update([
                            'website_uuid' =>
                                (string) Str::uuid(),
                        ]);
                }
            );


        /*
         * ------------------------------------------------------------
         * BACKFILL EXISTING CENTRAL-HOSTED WEBSITES AS SaaS
         * ------------------------------------------------------------
         *
         * Existing Esubiz websites predate deployment_type.
         *
         * Do NOT overwrite an explicit future/off-server value.
         */
        DB::table('websites')
            ->whereNull(
                'deployment_type'
            )
            ->update([
                'deployment_type' =>
                    'saas',

                'registered_at' =>
                    now(),
            ]);


        /*
         * Derive current SaaS domain from the existing subdomain.
         *
         * Custom-domain handling can later update registered_domain
         * without changing website identity.
         */
        DB::table('websites')
            ->where(
                'deployment_type',
                'saas'
            )
            ->whereNull(
                'registered_domain'
            )
            ->whereNotNull(
                'subdomain'
            )
            ->where(
                'subdomain',
                '<>',
                ''
            )
            ->orderBy('id')
            ->get([
                'id',
                'subdomain',
            ])
            ->each(
                function ($website) {

                    $subdomain =
                        strtolower(
                            trim(
                                (string)
                                $website->subdomain
                            )
                        );


                    if ($subdomain === '') {
                        return;
                    }


                    DB::table('websites')
                        ->where(
                            'id',
                            $website->id
                        )
                        ->update([
                            'registered_domain' =>
                                $subdomain
                                . '.esubiz.com',
                        ]);
                }
            );


        /*
         * UUID must become unique only AFTER every existing row
         * has been backfilled.
         */
        Schema::table(
            'websites',
            function (Blueprint $table) {

                $table->unique(
                    'website_uuid',
                    'websites_website_uuid_uq'
                );

                $table->index(
                    [
                        'owner_id',
                        'deployment_type',
                        'registry_status',
                    ],
                    'websites_owner_deployment_status_idx'
                );

                $table->index(
                    'registered_domain',
                    'websites_registered_domain_idx'
                );
            }
        );
    }


    public function down(): void
    {
        Schema::table(
            'websites',
            function (Blueprint $table) {

                $table->dropUnique(
                    'websites_website_uuid_uq'
                );

                $table->dropIndex(
                    'websites_owner_deployment_status_idx'
                );

                $table->dropIndex(
                    'websites_registered_domain_idx'
                );

                $table->dropColumn([
                    'website_uuid',
                    'deployment_type',
                    'registered_domain',
                    'registry_status',
                    'registered_at',
                    'last_central_seen_at',
                ]);
            }
        );
    }
};
