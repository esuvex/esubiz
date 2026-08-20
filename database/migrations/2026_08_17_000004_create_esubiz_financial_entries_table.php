<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esubiz_financial_entries', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('website_id')->nullable();
            $table->unsignedBigInteger('workspace_id')->nullable();

            $table->string('entry_type', 30);
            $table->string('source', 100);
            $table->string('description')->nullable();

            $table->string('reference_type')->nullable();
            $table->string('reference_id')->nullable();

            $table->decimal('amount', 18, 2);
            $table->char('currency', 3);

            $table->string('status', 30)->default('processed');

            $table->boolean('is_platform_entry')->default(true);

            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index('entry_type');
            $table->index('source');
            $table->index('currency');
            $table->index('status');
            $table->index('user_id');
            $table->index('website_id');
            $table->index('workspace_id');
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esubiz_financial_entries');
    }
};
