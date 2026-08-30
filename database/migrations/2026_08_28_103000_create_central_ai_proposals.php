<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * ============================================================
         * ESUBIZ GLOBAL AI PROPOSAL ENGINE V1
         * ============================================================
         *
         * Universal approval workflow for AI-generated work across:
         *
         * - Central Esubiz
         * - SaaS websites
         * - Off-server websites
         * - Themes
         * - Theme generation
         * - Page Builder
         * - Logo / favicon / media
         * - Ecommerce / Hotel / Forms / Blog
         * - Marketplace / developer tools
         * - Future registered AI capabilities
         *
         * AI proposes.
         * Esubiz validates.
         * User approves.
         * Capability applies to the real destination.
         */

        Schema::create(
            'central_ai_proposals',
            function (Blueprint $table) {
                $table->id();

                $table
                    ->uuid('uuid')
                    ->unique();

                /*
                 * Ownership / execution context.
                 *
                 * website_id is nullable because some AI work
                 * belongs to Central Esubiz rather than a website.
                 */
                $table
                    ->unsignedBigInteger('website_id')
                    ->nullable()
                    ->index();

                $table
                    ->unsignedBigInteger('installation_id')
                    ->nullable()
                    ->index();

                $table
                    ->unsignedBigInteger('user_id')
                    ->nullable()
                    ->index();

                $table
                    ->unsignedBigInteger('workspace_id')
                    ->nullable()
                    ->index();

                /*
                 * Where this proposal originated.
                 *
                 * Examples:
                 * website
                 * central
                 * marketplace
                 * developer
                 */
                $table
                    ->string('context_type', 64)
                    ->index();

                $table
                    ->string('context_id', 191)
                    ->nullable()
                    ->index();

                /*
                 * Authoritative AI scope.
                 *
                 * capability + action determine what AI was
                 * allowed to do when this proposal was created.
                 */
                $table
                    ->string('capability', 150)
                    ->index();

                $table
                    ->string('action', 100)
                    ->index();

                $table
                    ->string('proposal_type', 64)
                    ->default('change')
                    ->index();

                $table
                    ->string('title', 191)
                    ->nullable();

                $table
                    ->text('summary')
                    ->nullable();

                /*
                 * pending
                 * approved
                 * rejected
                 * applied
                 * failed
                 * superseded
                 */
                $table
                    ->string('status', 32)
                    ->default('pending')
                    ->index();

                /*
                 * Security boundary snapshots.
                 *
                 * Approval must validate against the scope and
                 * destination that existed when AI generated it.
                 */
                $table
                    ->json('manifest_snapshot')
                    ->nullable();

                $table
                    ->json('context_snapshot')
                    ->nullable();

                /*
                 * Destination tells the capability exactly where
                 * approved output belongs.
                 *
                 * Examples:
                 *
                 * Theme/About:
                 * theme -> homepage -> about
                 *
                 * Page Builder:
                 * page -> section -> widget
                 *
                 * Theme generation:
                 * website -> installed themes
                 *
                 * Logo:
                 * website -> branding -> logo
                 */
                $table
                    ->json('destination')
                    ->nullable();

                /*
                 * Data used by the universal preview interface.
                 */
                $table
                    ->json('preview_payload')
                    ->nullable();

                $table
                    ->json('metadata')
                    ->nullable();

                /*
                 * Approval/application audit.
                 */
                $table
                    ->unsignedBigInteger('approved_by')
                    ->nullable()
                    ->index();

                $table
                    ->timestamp('approved_at')
                    ->nullable();

                $table
                    ->timestamp('rejected_at')
                    ->nullable();

                $table
                    ->timestamp('applied_at')
                    ->nullable();

                $table
                    ->timestamp('failed_at')
                    ->nullable();

                $table
                    ->text('failure_message')
                    ->nullable();

                $table->timestamps();

                $table->index(
                    [
                        'website_id',
                        'capability',
                        'status',
                    ],
                    'central_ai_proposal_website_capability_status_idx'
                );

                $table->index(
                    [
                        'context_type',
                        'status',
                    ],
                    'central_ai_proposal_context_status_idx'
                );
            }
        );


        /*
         * One proposal may contain many outputs.
         *
         * Example:
         * Homepage redesign proposal:
         *
         * - title text
         * - body text
         * - CTA
         * - image
         *
         * They remain one approval experience.
         */
        Schema::create(
            'central_ai_proposal_items',
            function (Blueprint $table) {
                $table->id();

                $table
                    ->unsignedBigInteger('proposal_id')
                    ->index();

                $table
                    ->uuid('uuid')
                    ->unique();

                /*
                 * Generic output type.
                 *
                 * Examples:
                 * text
                 * image
                 * video_url
                 * file
                 * structured_data
                 * setting
                 * theme
                 * media
                 */
                $table
                    ->string('item_type', 64)
                    ->index();

                /*
                 * Logical destination inside the capability.
                 *
                 * Examples:
                 * hero_title
                 * about_text
                 * about_image_path
                 * logo
                 * favicon
                 * installed_theme
                 */
                $table
                    ->string('target_key', 191)
                    ->nullable()
                    ->index();

                $table
                    ->string('target_type', 100)
                    ->nullable();

                $table
                    ->string('target_id', 191)
                    ->nullable();

                /*
                 * Original value supports preview comparison,
                 * conflict detection and future undo/versioning.
                 */
                $table
                    ->json('original_value')
                    ->nullable();

                $table
                    ->json('proposed_value')
                    ->nullable();

                /*
                 * Generated media/file reference before approval.
                 *
                 * Applying the proposal moves/registers it in the
                 * normal destination used by the website/editor.
                 */
                $table
                    ->json('asset_reference')
                    ->nullable();

                $table
                    ->json('preview_payload')
                    ->nullable();

                $table
                    ->unsignedInteger('sort_order')
                    ->default(0);

                /*
                 * pending
                 * approved
                 * rejected
                 * applied
                 * failed
                 */
                $table
                    ->string('status', 32)
                    ->default('pending')
                    ->index();

                $table
                    ->json('metadata')
                    ->nullable();

                $table->timestamps();

                $table->index(
                    [
                        'proposal_id',
                        'sort_order',
                    ],
                    'central_ai_proposal_item_order_idx'
                );
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'central_ai_proposal_items'
        );

        Schema::dropIfExists(
            'central_ai_proposals'
        );
    }
};
