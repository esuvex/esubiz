<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_pages')) {
            return;
        }

        Schema::create('site_pages', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();

            $table->string('status', 20)
                ->default('draft');

            /*
             * ESUBIZ_CENTRAL_SITE_PAGE_BUILDER_DOCUMENT_V1
             *
             * Central website builder document only.
             * Completely independent from tenant/Core Page Builder storage.
             */
            $table->json('builder_json')->nullable();

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_pages');
    }
};