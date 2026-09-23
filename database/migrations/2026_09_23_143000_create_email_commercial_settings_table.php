<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_commercial_settings', function (Blueprint $table) {
            $table->id();

            $table->string('key', 100)->unique();

            $table->text('value')->nullable();

            $table->string('value_type', 30)
                ->default('string');

            $table->text('description')->nullable();

            $table->timestamps();
        });

        /*
         * Email commercial settings intentionally mirror the separation used
         * by Esubiz AI:
         *
         * provider cost       = synchronized automatically
         * Esubiz markup       = Central Admin controlled
         * Email Credit value  = Central Admin controlled
         *
         * Purchased Email Credit package quantities remain authoritative
         * and are never derived from package price.
         */

        DB::table('email_commercial_settings')->insert([
            [
                'key' => 'provider_markup_type',
                'value' => 'percentage',
                'value_type' => 'string',
                'description' => 'Esubiz markup method applied on top of the synchronized AWS SES provider cost.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'provider_markup_value',
                'value' => '0',
                'value_type' => 'number',
                'description' => 'Esubiz markup value. Percentage or fixed depending on provider_markup_type.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_rate_per_recipient_base_currency',
                'value' => '0',
                'value_type' => 'number',
                'description' => 'Optional maximum selling rate per billable email recipient in the current Central base/default currency after AWS provider cost and Esubiz markup. Zero means no cap.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'naira_per_email_credit',
                'value' => '1',
                'value_type' => 'number',
                'description' => 'Internal Naira consumption value of one Email Credit. Package discounts do not change this value.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('email_commercial_settings');
    }
};
