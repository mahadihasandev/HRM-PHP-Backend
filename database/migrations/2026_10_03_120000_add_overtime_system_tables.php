<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add overtime columns to employees table
        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                if (!Schema::hasColumn('employees', 'overtime_rate')) {
                    $table->decimal('overtime_rate', 10, 2)->default(0)->after('net_payable');
                }
                if (!Schema::hasColumn('employees', 'overtime_eligible')) {
                    $table->boolean('overtime_eligible')->default(true)->after('overtime_rate');
                }
            });
        }

        // 2. Add overtime columns to payslips table
        if (Schema::hasTable('payslips')) {
            Schema::table('payslips', function (Blueprint $table) {
                if (!Schema::hasColumn('payslips', 'overtime_hours')) {
                    $table->decimal('overtime_hours', 8, 2)->default(0)->after('special_allowance');
                }
                if (!Schema::hasColumn('payslips', 'overtime_rate')) {
                    $table->decimal('overtime_rate', 10, 2)->default(0)->after('overtime_hours');
                }
                if (!Schema::hasColumn('payslips', 'overtime_amount')) {
                    $table->decimal('overtime_amount', 12, 2)->default(0)->after('overtime_rate');
                }
            });
        }

        // 3. Add overtime_minutes to attendance_records table
        if (Schema::hasTable('attendance_records')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                if (!Schema::hasColumn('attendance_records', 'overtime_minutes')) {
                    $table->integer('overtime_minutes')->default(0)->after('overtime_hours');
                }
            });
        }

        // 4. Overtime audit log table for tracking when Admin/HR sets or updates rates
        if (!Schema::hasTable('overtime_rate_logs')) {
            Schema::create('overtime_rate_logs', function (Blueprint $table) {
                $table->id();
                $table->string('employee_full_id');
                $table->string('employee_name');
                $table->decimal('previous_rate', 10, 2)->default(0);
                $table->decimal('new_rate', 10, 2);
                $table->string('changed_by_id');
                $table->string('changed_by_name');
                $table->string('changed_by_role');
                $table->string('reason')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('overtime_rate_logs')) {
            Schema::dropIfExists('overtime_rate_logs');
        }

        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn(['overtime_rate', 'overtime_eligible']);
            });
        }

        if (Schema::hasTable('payslips')) {
            Schema::table('payslips', function (Blueprint $table) {
                $table->dropColumn(['overtime_hours', 'overtime_rate', 'overtime_amount']);
            });
        }

        if (Schema::hasTable('attendance_records')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->dropColumn('overtime_minutes');
            });
        }
    }
};
