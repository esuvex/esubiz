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
        | CRM Contacts
        |--------------------------------------------------------------------------
        */

        Schema::create('crm_contacts', function (Blueprint $table) {
            $table->id();

            $table->string('first_name');
            $table->string('last_name')->nullable();

            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->string('company')->nullable();
            $table->string('job_title')->nullable();

            $table->string('status')->default('active');

            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();

            $table->timestamps();

            $table->index('email');
            $table->index('phone');
            $table->index('status');
        });

        /*
        |--------------------------------------------------------------------------
        | CRM Companies
        |--------------------------------------------------------------------------
        */

        Schema::create('crm_companies', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();

            $table->string('industry')->nullable();

            $table->string('status')->default('active');

            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();

            $table->timestamps();

            $table->index('status');
        });

        /*
        |--------------------------------------------------------------------------
        | CRM Leads
        |--------------------------------------------------------------------------
        */

        Schema::create('crm_leads', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->string('source')->nullable();

            $table->string('status')->default('new');

            $table->string('lead_value')->nullable();

            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('source');
        });

        /*
        |--------------------------------------------------------------------------
        | CRM Deals
        |--------------------------------------------------------------------------
        */

        Schema::create('crm_deals', function (Blueprint $table) {
            $table->id();

            $table->string('title');

            $table->foreignId('contact_id')
                ->nullable()
                ->constrained('crm_contacts')
                ->nullOnDelete();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained('crm_companies')
                ->nullOnDelete();

            $table->decimal('value', 20, 2)->default(0);

            $table->string('stage')->default('new');

            $table->string('status')->default('open');

            $table->date('expected_close_date')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('stage');
            $table->index('status');
        });

        /*
        |--------------------------------------------------------------------------
        | CRM Tasks
        |--------------------------------------------------------------------------
        */

        Schema::create('crm_tasks', function (Blueprint $table) {
            $table->id();

            $table->string('title');

            $table->text('description')->nullable();

            $table->foreignId('contact_id')
                ->nullable()
                ->constrained('crm_contacts')
                ->nullOnDelete();

            $table->foreignId('deal_id')
                ->nullable()
                ->constrained('crm_deals')
                ->nullOnDelete();

            $table->string('status')->default('pending');

            $table->string('priority')->default('normal');

            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('priority');
        });

        /*
        |--------------------------------------------------------------------------
        | CRM Notes
        |--------------------------------------------------------------------------
        */

        Schema::create('crm_notes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contact_id')
                ->nullable()
                ->constrained('crm_contacts')
                ->cascadeOnDelete();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained('crm_companies')
                ->cascadeOnDelete();

            $table->foreignId('deal_id')
                ->nullable()
                ->constrained('crm_deals')
                ->cascadeOnDelete();

            $table->longText('content');

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | CRM Activities
        |--------------------------------------------------------------------------
        */

        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();

            $table->string('type');

            $table->string('subject')->nullable();

            $table->longText('description')->nullable();

            $table->foreignId('contact_id')
                ->nullable()
                ->constrained('crm_contacts')
                ->nullOnDelete();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained('crm_companies')
                ->nullOnDelete();

            $table->foreignId('deal_id')
                ->nullable()
                ->constrained('crm_deals')
                ->nullOnDelete();

            $table->timestamp('occurred_at')->nullable();

            $table->timestamps();

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_activities');
        Schema::dropIfExists('crm_notes');
        Schema::dropIfExists('crm_tasks');
        Schema::dropIfExists('crm_deals');
        Schema::dropIfExists('crm_leads');
        Schema::dropIfExists('crm_companies');
        Schema::dropIfExists('crm_contacts');
    }
};
