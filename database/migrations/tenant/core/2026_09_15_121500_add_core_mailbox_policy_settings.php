<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ESUBIZ_CORE_MAILBOX_POLICY_DEFAULTS_V1
 *
 * Mailbox policy lives in email_boxes.settings so every mailbox may
 * independently control retention and source-folder visibility.
 *
 * No new storage-owning table is introduced.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('email_boxes')) {
            return;
        }

        $db = DB::connection(
            Schema::getConnection()->getName()
        );

        $boxes = $db
            ->table('email_boxes')
            ->select(['id', 'settings'])
            ->get();

        foreach ($boxes as $box) {
            $settings = json_decode(
                (string) ($box->settings ?? ''),
                true
            );

            if (!is_array($settings)) {
                $settings = [];
            }

            $settings += [
                'retention_days' => [
                    'inbox' => 0,
                    'spam' => 30,
                    'draft' => 0,
                    'sent' => 0,
                    'trash' => 30,
                ],

                'source_visibility' => [
                    'direct_email' => true,
                    'core_system' => true,
                    'system_auth' => true,
                    'crm' => true,
                    'hr' => true,
                    'tickets' => true,
                    'live_chat' => true,
                    'contact_form' => true,
                    'marketing' => true,
                    'module' => true,
                    'esubiz' => true,
                ],
            ];

            $db->table('email_boxes')
                ->where('id', $box->id)
                ->update([
                    'settings' => json_encode(
                        $settings,
                        JSON_UNESCAPED_SLASHES
                    ),
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        /*
         * Policy is JSON configuration owned by each mailbox.
         * Do not destructively remove administrator preferences.
         */
    }
};
