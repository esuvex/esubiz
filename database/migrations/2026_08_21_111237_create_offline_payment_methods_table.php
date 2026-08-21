<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offline_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('name');
            $table->string('slug')->unique();

            $table->enum('type', [
                'bank_transfer',
                'cash',
                'manual',
                'other',
            ])->default('manual');

            $table->text('instructions')->nullable();

            /*
             * Controls whether customers must upload proof of payment
             * after completing an offline payment.
             */
            $table->boolean('receipt_upload_enabled')->default(false);
            $table->string('receipt_upload_label')->nullable();
            $table->string('receipt_upload_help')->nullable();

            $table->longText('settings')->nullable();

            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('priority')->default(1);
            $table->boolean('is_default')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offline_payment_methods');
    }
};
