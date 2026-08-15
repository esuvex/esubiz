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
        | HR Departments
        |--------------------------------------------------------------------------
        */

        Schema::create('hr_departments', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code')->nullable()->unique();

            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | HR Employees
        |--------------------------------------------------------------------------
        */

        Schema::create('hr_employees', function (Blueprint $table) {
            $table->id();

            $table->string('employee_code')->unique();

            $table->string('first_name');
            $table->string('last_name');

            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('hr_departments')
                ->nullOnDelete();

            $table->string('job_title')->nullable();

            $table->date('employment_date')->nullable();

            $table->string('employment_status')->default('active');

            $table->decimal('salary', 20, 2)->nullable();

            $table->json('personal_details')->nullable();
            $table->json('employment_details')->nullable();

            $table->timestamps();

            $table->index('employment_status');
        });

        /*
        |--------------------------------------------------------------------------
        | HR Leave Types
        |--------------------------------------------------------------------------
        */

        Schema::create('hr_leave_types', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code')->nullable()->unique();

            $table->decimal('days_per_year', 10, 2)->default(0);

            $table->boolean('is_paid')->default(true);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | HR Leave Requests
        |--------------------------------------------------------------------------
        */

        Schema::create('hr_leave_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained('hr_employees')
                ->cascadeOnDelete();

            $table->foreignId('leave_type_id')
                ->constrained('hr_leave_types')
                ->cascadeOnDelete();

            $table->date('start_date');
            $table->date('end_date');

            $table->decimal('days', 10, 2);

            $table->text('reason')->nullable();

            $table->string('status')->default('pending');

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index('status');
        });

        /*
        |--------------------------------------------------------------------------
        | HR Attendance
        |--------------------------------------------------------------------------
        */

        Schema::create('hr_attendance', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained('hr_employees')
                ->cascadeOnDelete();

            $table->date('attendance_date');

            $table->timestamp('clock_in')->nullable();
            $table->timestamp('clock_out')->nullable();

            $table->string('status')->default('present');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique([
                'employee_id',
                'attendance_date',
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | HR Payroll Records
        |--------------------------------------------------------------------------
        */

        Schema::create('hr_payroll_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained('hr_employees')
                ->cascadeOnDelete();

            $table->string('period');

            $table->decimal('gross_amount', 20, 2)->default(0);
            $table->decimal('deductions', 20, 2)->default(0);
            $table->decimal('net_amount', 20, 2)->default(0);

            $table->string('status')->default('draft');

            $table->timestamp('paid_at')->nullable();

            $table->json('breakdown')->nullable();

            $table->timestamps();

            $table->unique([
                'employee_id',
                'period',
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | HR Performance Reviews
        |--------------------------------------------------------------------------
        */

        Schema::create('hr_performance_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained('hr_employees')
                ->cascadeOnDelete();

            $table->string('review_period');

            $table->decimal('rating', 5, 2)->nullable();

            $table->text('strengths')->nullable();
            $table->text('improvements')->nullable();
            $table->text('comments')->nullable();

            $table->string('status')->default('draft');

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | HR Documents
        |--------------------------------------------------------------------------
        */

        Schema::create('hr_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained('hr_employees')
                ->cascadeOnDelete();

            $table->string('name');

            $table->string('file_path');
            $table->string('mime_type')->nullable();

            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_documents');
        Schema::dropIfExists('hr_performance_reviews');
        Schema::dropIfExists('hr_payroll_records');
        Schema::dropIfExists('hr_attendance');
        Schema::dropIfExists('hr_leave_requests');
        Schema::dropIfExists('hr_leave_types');
        Schema::dropIfExists('hr_employees');
        Schema::dropIfExists('hr_departments');
    }
};
