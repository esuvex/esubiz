<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /*
     * ESUBIZ_EXISTING_SAAS_INCLUDED_MAILBOX_BACKFILL_V1
     *
     * This migration intentionally makes no tenant data changes.
     *
     * Its migration record is the canonical Central Migration & Updates
     * eligibility/checkpoint for the one-time existing-SaaS mailbox
     * backfill.
     *
     * After this migration succeeds, PlatformUpdateService executes the
     * explicitly allow-listed Central action declared in the update
     * manifest:
     *
     *     provision_included_saas_mailbox
     *
     * WebsiteMailboxService remains the sole authority for DirectAdmin
     * provisioning and tenant email_boxes synchronization.
     */

    public function up(): void
    {
        // Checkpoint only. Central owns the external mailbox side effect.
    }

    public function down(): void
    {
        /*
         * Intentionally non-destructive.
         *
         * Rolling back a migration record must never delete a real
         * customer mailbox or its messages.
         */
    }
};
