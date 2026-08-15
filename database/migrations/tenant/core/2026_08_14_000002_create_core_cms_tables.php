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
        | Pages
        |--------------------------------------------------------------------------
        */

        Schema::create('pages', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique('uq_pages_slug');

            $table->string('status')->default('draft');

            $table->longText('content')->nullable();

            $table->boolean('is_homepage')->default(false);

            $table->json('settings')->nullable();
            $table->json('seo')->nullable();

            $table->timestamp('published_at')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Media
        |--------------------------------------------------------------------------
        */

        Schema::create('media', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('file_name');
            $table->string('disk')->default('public');
            $table->string('path');

            $table->string('mime_type')->nullable();

            $table->unsignedBigInteger('size')->default(0);

            $table->string('alt_text')->nullable();
            $table->text('description')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Menus
        |--------------------------------------------------------------------------
        */

        Schema::create('menus', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('location')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Menu Items
        |--------------------------------------------------------------------------
        */

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('menu_id')
                ->constrained('menus', 'id', 'fk_mi_menu')
                ->cascadeOnDelete();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('menu_items', 'id', 'fk_mi_parent')
                ->nullOnDelete();

            $table->string('label');

            $table->string('type')->default('custom');

            $table->string('url')->nullable();

            $table->unsignedBigInteger('page_id')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('page_id', 'ix_mi_page');
        });

        /*
        |--------------------------------------------------------------------------
        | Widgets
        |--------------------------------------------------------------------------
        */

        Schema::create('widgets', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('type');

            $table->text('description')->nullable();

            $table->json('settings')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Forms
        |--------------------------------------------------------------------------
        */

        Schema::create('forms', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique('uq_forms_slug');

            $table->text('description')->nullable();

            $table->json('settings')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Form Fields
        |--------------------------------------------------------------------------
        */

        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();

            $table->foreignId('form_id')
                ->constrained('forms', 'id', 'fk_ff_form')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('label');

            $table->string('type')->default('text');

            $table->boolean('required')->default(false);

            $table->json('options')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Form Submissions
        |--------------------------------------------------------------------------
        */

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('form_id')
                ->constrained('forms', 'id', 'fk_fs_form')
                ->cascadeOnDelete();

            $table->json('data');

            $table->string('status')->default('new');

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Page Builder Documents
        |--------------------------------------------------------------------------
        */

        Schema::create('page_builder_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('page_id')
                ->constrained('pages', 'id', 'fk_pbd_page')
                ->cascadeOnDelete();

            $table->json('content')->nullable();

            $table->string('builder_version')->default('1.0.0');

            $table->timestamps();

            $table->unique('page_id', 'uq_pbd_page');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_builder_documents');
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('forms');
        Schema::dropIfExists('widgets');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('media');
        Schema::dropIfExists('pages');
    }
};
