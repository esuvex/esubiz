<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esubiz_commission_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('revenue_event_id');
            $table->string('invoice_reference')->unique();
            $table->decimal('base_amount', 18, 2);
            $table->decimal('commission_rate', 18, 2)->default(0);
            $table->enum('commission_type', ['fixed', 'percentage']);
            $table->decimal('commission_amount', 18, 2);
            $table->char('currency', 3)->default('NGN');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->enum('status', [
                'unpaid',
                'paid',
                'overdue',
                'cancelled',
            ])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('revenue_event_id');
            $table->index(['workspace_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esubiz_commission_invoices');
    }
};
