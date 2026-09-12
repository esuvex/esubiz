<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
     * ESUBIZ_CENTRAL_ACCOUNT_FIELDS_V1
     *
     * Shared Central account metadata for:
     *
     * - Platform Main Admin
     * - Staff
     * - Investor / Partner
     * - User
     * - Developer
     *
     * Approval state is intentionally separate from account
     * status so a user may sign in to a dashboard while still
     * waiting for Developer/Investor approval.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'account_role')) {
                $table->string(
                    'account_role',
                    50
                )
                    ->default('user')
                    ->index()
                    ->after('password');
            }

            if (!Schema::hasColumn('users', 'approval_status')) {
                $table->string(
                    'approval_status',
                    50
                )
                    ->default('approved')
                    ->index()
                    ->after('account_role');
            }

            if (!Schema::hasColumn('users', 'account_status')) {
                $table->string(
                    'account_status',
                    50
                )
                    ->default('active')
                    ->index()
                    ->after('approval_status');
            }

            if (!Schema::hasColumn('users', 'country_code')) {
                $table->string(
                    'country_code',
                    2
                )
                    ->nullable()
                    ->index()
                    ->after('account_status');
            }

            if (!Schema::hasColumn('users', 'phone_country_code')) {
                $table->string(
                    'phone_country_code',
                    10
                )
                    ->nullable()
                    ->after('country_code');
            }

            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string(
                    'phone_number',
                    40
                )
                    ->nullable()
                    ->after('phone_country_code');
            }

            if (!Schema::hasColumn('users', 'approved_at')) {
                $table->timestamp(
                    'approved_at'
                )
                    ->nullable()
                    ->after('phone_number');
            }

            if (!Schema::hasColumn('users', 'approved_by')) {
                $table->unsignedBigInteger(
                    'approved_by'
                )
                    ->nullable()
                    ->index()
                    ->after('approved_at');
            }

            if (!Schema::hasColumn('users', 'approval_note')) {
                $table->text(
                    'approval_note'
                )
                    ->nullable()
                    ->after('approved_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'approval_note',
                'approved_by',
                'approved_at',
                'phone_number',
                'phone_country_code',
                'country_code',
                'account_status',
                'approval_status',
                'account_role',
            ];

            foreach ($columns as $column) {
                if (
                    Schema::hasColumn(
                        'users',
                        $column
                    )
                ) {
                    $table->dropColumn(
                        $column
                    );
                }
            }
        });
    }
};
