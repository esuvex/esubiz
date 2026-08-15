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

            $table->index('email', 'ix_crm_contacts_email');
            $table->index('phone', 'ix_crm_contacts_phone');
            $table->index('status', 'ix_crm_contacts_status');
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

            $table->index('status', 'ix_crm_companie_status');
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

            $table->index('status', 'ix_crm_leads_status');
            $table->index('source', 'ix_crm_leads_source');
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
                ->constrained('crm_contacts', 'id', 'fk_crm_deals_contact')
                ->nullOnDelete();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained('crm_companies', 'id', 'fk_crm_deals_company')
                ->nullOnDelete();

            $table->decimal('value', 20, 2)->default(0);

            $table->string('stage')->default('new');

            $table->string('status')->default('open');

            $table->date('expected_close_date')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('stage', 'ix_crm_deals_stage');
            $table->index('status', 'ix_crm_deals_status');
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
                ->constrained('crm_contacts', 'id', 'fk_crm_tasks_contact')
                ->nullOnDelete();

            $table->foreignId('deal_id')
                ->nullable()
                ->constrained('crm_deals', 'id', 'fk_crm_tasks_deal')
                ->nullOnDelete();

            $table->string('status')->default('pending');

            $table->string('priority')->default('normal');

            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index('status', 'ix_crm_tasks_status');
            $table->index('priority', 'ix_crm_tasks_priority');
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
                ->constrained('crm_contacts', 'id', 'fk_crm_notes_contact')
                ->cascadeOnDelete();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained('crm_companies', 'id', 'fk_crm_notes_company')
                ->cascadeOnDelete();

            $table->foreignId('deal_id')
                ->nullable()
                ->constrained('crm_deals', 'id', 'fk_crm_notes_deal')
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
                ->constrained('crm_contacts', 'id', 'fk_crm_activiti_contact')
                ->nullOnDelete();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained('crm_companies', 'id', 'fk_crm_activiti_company')
                ->nullOnDelete();

            $table->foreignId('deal_id')
                ->nullable()
                ->constrained('crm_deals', 'id', 'fk_crm_activiti_deal')
                ->nullOnDelete();

            $table->timestamp('occurred_at')->nullable();

            $table->timestamps();

            $table->index('type', 'ix_crm_activiti_type');
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
