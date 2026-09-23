<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('aws_service_settings')) {
            return;
        }

        Schema::create(
            'aws_service_settings',
            function (Blueprint $table) {
                $table->id();

                /*
                 * Allows future AWS service contexts without creating
                 * separate credential tables.
                 *
                 * "central" is the authoritative Esubiz AWS account.
                 */
                $table
                    ->string('service_key', 100)
                    ->unique();

                /*
                 * Encrypted by the AwsServiceSetting model.
                 *
                 * Expected payload:
                 * [
                 *     'access_key_id' => '...',
                 *     'secret_access_key' => '...',
                 * ]
                 */
                $table
                    ->longText('secret_payload')
                    ->nullable();

                /*
                 * Operational SES region is deliberately separate from
                 * the AWS Price List API endpoint region.
                 */
                $table
                    ->string('ses_region', 50)
                    ->nullable();

                $table
                    ->string('pricing_api_region', 50)
                    ->nullable();

                $table
                    ->string('sender_address')
                    ->nullable();

                $table
                    ->string('sender_name')
                    ->nullable();

                $table
                    ->boolean('is_enabled')
                    ->default(true);

                /*
                 * Non-secret AWS/provider metadata only.
                 */
                $table
                    ->json('metadata')
                    ->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'aws_service_settings'
        );
    }
};
