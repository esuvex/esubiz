<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'crm_companies' => function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('email')->nullable();
                $t->string('phone')->nullable();
                $t->string('website')->nullable();
                $t->string('industry')->nullable();
                $t->string('status')->default('active');
                $t->text('notes')->nullable();
                $t->json('custom_fields')->nullable();
                $t->timestamps();
                $t->index('status');
            },

            'crm_deals' => function (Blueprint $t) {
                $t->id();
                $t->string('title');
                $t->foreignId('contact_id')->nullable()->constrained('crm_contacts')->nullOnDelete();
                $t->foreignId('company_id')->nullable()->constrained('crm_companies')->nullOnDelete();
                $t->decimal('value', 20, 2)->default(0);
                $t->string('stage')->default('new');
                $t->string('status')->default('open');
                $t->date('expected_close_date')->nullable();
                $t->text('notes')->nullable();
                $t->timestamps();
                $t->index('stage');
                $t->index('status');
            },

            'crm_projects' => function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->text('description')->nullable();
                $t->foreignId('client_id')->nullable()->constrained('crm_contacts')->nullOnDelete();
                $t->string('status')->default('active');
                $t->date('start_date')->nullable();
                $t->date('due_date')->nullable();
                $t->decimal('budget', 20, 2)->default(0);
                $t->timestamps();
                $t->index('status');
            },

            'crm_team_members' => function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('email')->nullable();
                $t->string('phone')->nullable();
                $t->string('role')->nullable();
                $t->string('status')->default('active');
                $t->timestamps();
                $t->index('status');
            },

            'crm_invoices' => function (Blueprint $t) {
                $t->id();
                $t->string('invoice_number')->unique();
                $t->foreignId('client_id')->nullable()->constrained('crm_contacts')->nullOnDelete();
                $t->decimal('subtotal', 20, 2)->default(0);
                $t->decimal('tax', 20, 2)->default(0);
                $t->decimal('discount', 20, 2)->default(0);
                $t->decimal('total', 20, 2)->default(0);
                $t->string('currency', 3)->default('NGN');
                $t->string('status')->default('draft');
                $t->date('issue_date')->nullable();
                $t->date('due_date')->nullable();
                $t->timestamps();
                $t->index('status');
            },

            'crm_estimates' => function (Blueprint $t) {
                $t->id();
                $t->string('estimate_number')->unique();
                $t->foreignId('client_id')->nullable()->constrained('crm_contacts')->nullOnDelete();
                $t->decimal('subtotal', 20, 2)->default(0);
                $t->decimal('tax', 20, 2)->default(0);
                $t->decimal('discount', 20, 2)->default(0);
                $t->decimal('total', 20, 2)->default(0);
                $t->string('currency', 3)->default('NGN');
                $t->string('status')->default('draft');
                $t->date('issue_date')->nullable();
                $t->date('expiry_date')->nullable();
                $t->timestamps();
                $t->index('status');
            },

            'crm_proposals' => function (Blueprint $t) {
                $t->id();
                $t->string('proposal_number')->unique();
                $t->string('title');
                $t->foreignId('client_id')->nullable()->constrained('crm_contacts')->nullOnDelete();
                $t->longText('content')->nullable();
                $t->decimal('value', 20, 2)->default(0);
                $t->string('currency', 3)->default('NGN');
                $t->string('status')->default('draft');
                $t->date('issue_date')->nullable();
                $t->date('expiry_date')->nullable();
                $t->timestamps();
                $t->index('status');
            },

            'crm_contracts' => function (Blueprint $t) {
                $t->id();
                $t->string('contract_number')->unique();
                $t->string('title');
                $t->foreignId('client_id')->nullable()->constrained('crm_contacts')->nullOnDelete();
                $t->longText('content')->nullable();
                $t->date('start_date')->nullable();
                $t->date('end_date')->nullable();
                $t->string('status')->default('draft');
                $t->timestamps();
                $t->index('status');
            },

            'crm_expenses' => function (Blueprint $t) {
                $t->id();
                $t->string('title');
                $t->string('category')->nullable();
                $t->decimal('amount', 20, 2)->default(0);
                $t->string('currency', 3)->default('NGN');
                $t->string('status')->default('recorded');
                $t->date('expense_date')->nullable();
                $t->text('description')->nullable();
                $t->timestamps();
            },

            'crm_knowledgebase_articles' => function (Blueprint $t) {
                $t->id();
                $t->string('title');
                $t->string('slug')->unique();
                $t->longText('content');
                $t->string('category')->nullable();
                $t->string('status')->default('draft');
                $t->timestamps();
                $t->index('status');
            },

            'crm_subscriptions' => function (Blueprint $t) {
                $t->id();
                $t->foreignId('client_id')->nullable()->constrained('crm_contacts')->nullOnDelete();
                $t->string('name');
                $t->decimal('amount', 20, 2)->default(0);
                $t->string('currency', 3)->default('NGN');
                $t->string('billing_cycle')->default('monthly');
                $t->string('status')->default('active');
                $t->date('starts_at')->nullable();
                $t->date('ends_at')->nullable();
                $t->timestamps();
                $t->index('status');
            },

            'crm_tickets' => function (Blueprint $t) {
                $t->id();
                $t->string('ticket_number')->unique();
                $t->foreignId('client_id')->nullable()->constrained('crm_contacts')->nullOnDelete();
                $t->string('subject');
                $t->longText('description')->nullable();
                $t->string('priority')->default('normal');
                $t->string('status')->default('open');
                $t->timestamps();
                $t->index('status');
                $t->index('priority');
            },

            'crm_calendar_events' => function (Blueprint $t) {
                $t->id();
                $t->string('title');
                $t->text('description')->nullable();
                $t->timestamp('starts_at');
                $t->timestamp('ends_at')->nullable();
                $t->string('location')->nullable();
                $t->string('status')->default('scheduled');
                $t->timestamps();
                $t->index('starts_at');
                $t->index('status');
            },

            'crm_time_entries' => function (Blueprint $t) {
                $t->id();
                $t->foreignId('project_id')->nullable()->constrained('crm_projects')->nullOnDelete();
                $t->foreignId('task_id')->nullable()->constrained('crm_tasks')->nullOnDelete();
                $t->foreignId('team_member_id')->nullable()->constrained('crm_team_members')->nullOnDelete();
                $t->timestamp('started_at')->nullable();
                $t->timestamp('ended_at')->nullable();
                $t->decimal('hours', 10, 2)->default(0);
                $t->text('description')->nullable();
                $t->timestamps();
            },

            'crm_reminders' => function (Blueprint $t) {
                $t->id();
                $t->string('title');
                $t->text('description')->nullable();
                $t->timestamp('remind_at');
                $t->string('status')->default('pending');
                $t->timestamps();
                $t->index('remind_at');
                $t->index('status');
            },

            'crm_income' => function (Blueprint $t) {
                $t->id();
                $t->string('title');
                $t->decimal('amount', 20, 2)->default(0);
                $t->string('currency', 3)->default('NGN');
                $t->string('category')->nullable();
                $t->string('reference')->nullable();
                $t->date('income_date')->nullable();
                $t->text('description')->nullable();
                $t->timestamps();
            },

            'crm_payments' => function (Blueprint $t) {
                $t->id();
                $t->foreignId('invoice_id')->nullable()->constrained('crm_invoices')->nullOnDelete();
                $t->foreignId('client_id')->nullable()->constrained('crm_contacts')->nullOnDelete();
                $t->string('reference')->unique();
                $t->decimal('amount', 20, 2)->default(0);
                $t->string('currency', 3)->default('NGN');
                $t->string('method')->nullable();
                $t->string('status')->default('pending');
                $t->timestamp('paid_at')->nullable();
                $t->timestamps();
                $t->index('status');
            },

            'crm_receipts' => function (Blueprint $t) {
                $t->id();
                $t->string('receipt_number')->unique();
                $t->foreignId('payment_id')->nullable()->constrained('crm_payments')->nullOnDelete();
                $t->foreignId('client_id')->nullable()->constrained('crm_contacts')->nullOnDelete();
                $t->decimal('amount', 20, 2)->default(0);
                $t->string('currency', 3)->default('NGN');
                $t->date('receipt_date')->nullable();
                $t->timestamps();
            },
        ];

        foreach ($tables as $name => $callback) {
            if (!Schema::hasTable($name)) {
                Schema::create($name, $callback);
            }
        }
    }

    public function down(): void
    {
        foreach ([
            'crm_receipts',
            'crm_payments',
            'crm_income',
            'crm_reminders',
            'crm_time_entries',
            'crm_calendar_events',
            'crm_tickets',
            'crm_subscriptions',
            'crm_knowledgebase_articles',
            'crm_expenses',
            'crm_contracts',
            'crm_proposals',
            'crm_estimates',
            'crm_invoices',
            'crm_team_members',
            'crm_projects',
            'crm_deals',
            'crm_companies',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
