<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('company_name');
            $table->string('company_address')->nullable();
            $table->string('month', 7)->index();
            $table->string('title');
            $table->string('status')->default('draft');
            $table->string('source_name')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('debit_account')->nullable();
            $table->string('signatory')->nullable();
            $table->string('signatory_title')->nullable();
            $table->timestamps();
        });
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->unsignedBigInteger('company_id');
            $table->string('month', 7);
            $table->string('employee_full_id');
            $table->string('employee_name');
            foreach (['department', 'designation', 'factory', 'section', 'line', 'grade', 'bank_name', 'bank_account_no', 'routing_no'] as $field) {
                $table->string($field)->nullable();
            }
            $table->string('payment_method');
            foreach (['basic_salary', 'house_rent', 'medical_allowance', 'conveyance', 'food_allowance', 'attendance_bonus', 'production_bonus', 'festival_bonus', 'overtime_rate', 'overtime_hours', 'overtime_amount', 'absence_deduction', 'loan_deduction', 'pf_deduction', 'tax_deduction', 'other_deductions', 'total_earnings', 'total_deductions', 'net_payable'] as $field) {
                $table->decimal($field, 14, 2)->default(0);
            }
            $table->timestamps();
            $table->unique(['company_id', 'month', 'employee_full_id'], 'payroll_employee_month_unique');
        });
        Schema::create('payroll_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->string('name');
            $table->string('path');
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_attachments');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_runs');
    }
};
