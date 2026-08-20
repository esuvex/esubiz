<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_quotations')) {
            Schema::create('crm_quotations', function (Blueprint $table) {
                $table->id();

                $table->string('quotation_number')->unique();

                $table->foreignId('client_id')
                    ->nullable()
                    ->constrained('crm_contacts')
                    ->nullOnDelete();

                $table->string('title')->nullable();

                $table->decimal('subtotal', 10, 2)->default(0);
                $table->decimal('tax', 10, 2)->default(0);
                $table->decimal('discount', 10, 2)->default(0);
                $table->decimal('total', 10, 2)->default(0);

                $table->string('currency', 3)->default('NGN');
                $table->string('status')->default('draft');

                $table->date('issue_date')->nullable();
                $table->date('expiry_date')->nullable();

                $table->longText('notes')->nullable();

                $table->timestamps();

                $table->index('status');
                $table->index('client_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_quotations');
    }
};
