<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('website_media')) {
            return;
        }

        Schema::create(
            'website_media',
            function (Blueprint $table) {

                $table->id();

                $table->uuid('uuid')->unique();

                /*
                 * Canonical storage path:
                 *
                 * tenant-websites/{website_id}/media/...
                 */
                $table->string('path', 1000)->unique();

                $table->string('filename', 255);

                $table->string(
                    'original_name',
                    255
                )->nullable();

                $table->string(
                    'title',
                    255
                )->nullable();

                $table->string(
                    'alt_text',
                    500
                )->nullable();

                $table->text(
                    'caption'
                )->nullable();

                $table->string(
                    'mime_type',
                    150
                )->nullable();

                $table->string(
                    'media_type',
                    30
                )->default('file')->index();

                $table
                    ->unsignedBigInteger(
                        'size_bytes'
                    )
                    ->default(0);

                /*
                 * Where the media originally entered the site.
                 *
                 * Examples:
                 * media_library
                 * page_builder
                 * theme
                 * module
                 * form
                 * ai
                 */
                $table->string(
                    'source',
                    50
                )->default('media_library')->index();

                /*
                 * Optional source namespace:
                 * theme/logo
                 * theme/hero
                 * page-builder/image
                 * module/gallery
                 */
                $table->string(
                    'source_context',
                    150
                )->nullable();

                $table->json(
                    'metadata'
                )->nullable();

                $table->timestamps();
                $table->softDeletes();
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'website_media'
        );
    }
};
