<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_events', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('website_id')->nullable();
            $table->unsignedBigInteger('workspace_id')->nullable();

            $table->string('source', 100);
            $table->string('expense_type', 100);
            $table->string('description')->nullable();

            $table->string('reference_type')->nullable();
            $table->string('reference_id')->nullable();

            $table->decimal('amount', 18, 2);
            $table->char('currency', 3);

            $table->string('status', 30)->default('processed');

            $table->timestamp('occurred_at');

            $table->timestamps();

            $table->index('user_id');
            $table->index('website_id');
            $table->index('workspace_id');
            $table->index('source');
            $table->index('expense_type');
            $table->index('currency');
            $table->index('status');
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_events');
    }
};
