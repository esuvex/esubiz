<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_database_connections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('website_id')
                ->unique()
                ->constrained('websites')
                ->cascadeOnDelete();

            $table->string('host')->default('127.0.0.1');
            $table->unsignedSmallInteger('port')->default(3306);
            $table->string('database');
            $table->string('username');
            $table->text('password');

            $table->enum('status', [
                'pending',
                'active',
                'failed',
                'disabled',
            ])->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_database_connections');
    }
};
