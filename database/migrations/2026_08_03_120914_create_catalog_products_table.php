<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('catalog_products', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Category
            |--------------------------------------------------------------------------
            */

            $table->foreignId('catalog_category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->string('name');

            $table->string('slug')->unique();

            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Product Type
            |--------------------------------------------------------------------------
            */

            $table->enum('product_type', [

                'plan',

                'capacity',

                'module',

                'theme',

                'template',

                'membership',

                'domain',

                'professional_email',

                'ai_credit',

                'sms_credit',

                'whatsapp_credit',

                'service',

                'license',

                'other'

            ]);

            /*
            |--------------------------------------------------------------------------
            | Audience
            |--------------------------------------------------------------------------
            */

            $table->enum('audience', [

                'saas',

                'developer',

                'both'

            ])->default('both');

            /*
            |--------------------------------------------------------------------------
            | Fulfilment
            |--------------------------------------------------------------------------
            */

            $table->enum('fulfilment_type', [

                'instant',

                'manual',

                'subscription',

                'renewable'

            ])->default('instant');

            /*
            |--------------------------------------------------------------------------
            | Visibility
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_featured')->default(false);

            $table->boolean('is_active')->default(true);

            $table->boolean('is_public')->default(true);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_products');
    }
};
