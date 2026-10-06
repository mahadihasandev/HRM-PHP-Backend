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
        // 1. Employees Table
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->unique();
            $table->string('employee_full_id')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('personal_phone')->nullable();
            $table->string('designation');
            $table->string('department');
            $table->string('company')->default('Smart Technologies (BD) Ltd.');
            $table->unsignedBigInteger('company_id')->default(7);
            $table->string('status')->default('Active'); // Active, Inactive, On Leave
            $table->string('blood_group', 10)->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('marital_status', 20)->nullable();
            $table->string('religion', 30)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('present_address')->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('routing_name')->nullable();
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('house_rent', 12, 2)->default(0);
            $table->decimal('medical_allowance', 12, 2)->default(0);
            $table->decimal('conveyance', 12, 2)->default(0);
            $table->decimal('gross_salary', 12, 2)->default(0);
            $table->decimal('pf_deduction', 12, 2)->default(0);
            $table->decimal('tax_deduction', 12, 2)->default(0);
            $table->decimal('net_payable', 12, 2)->default(0);
            $table->timestamps();
        });

        // 2. HR Loans Table
        Schema::create('hr_loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('employee_full_id')->nullable();
            $table->string('employee_name')->nullable();
            $table->decimal('amount', 12, 2);
            $table->integer('installment_count')->default(10);
            $table->decimal('monthly_installment', 12, 2);
            $table->string('applicable_month', 10)->default('2026-11');
            $table->text('purpose');
            $table->decimal('cash_value', 12, 2)->default(0);
            $table->decimal('bank_value', 12, 2)->default(0);
            $table->string('status')->default('Pending'); // Pending, Approved, Active, Repaid, Rejected
            $table->date('applied_at');
            $table->timestamps();
        });

        // 3. Leave Applications Table
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('employee_full_id');
            $table->string('employee_name');
            $table->string('leave_type');
            $table->unsignedInteger('leave_type_id')->default(1);
            $table->date('from_date');
            $table->date('to_date');
            $table->integer('days_count')->default(1);
            $table->text('reason');
            $table->string('emergency_phone')->nullable();
            $table->string('status')->default('Pending Recommend');
            $table->timestamp('applied_at')->nullable();
            $table->string('recommended_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->text('recommend_note')->nullable();
            $table->text('approve_note')->nullable();
            $table->timestamps();
        });

        // 4. Attendance Records Table
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('employee_full_id');
            $table->string('employee_name');
            $table->date('date');
            $table->string('in_time')->nullable();
            $table->string('out_time')->nullable();
            $table->string('status')->default('Present'); // Present, Late, Absent, Leave, Holiday, Weekend
            $table->string('working_hours')->nullable();
            $table->string('overtime_hours')->nullable();
            $table->integer('late_minutes')->default(0);
            $table->string('location')->default('Tejgaon I/A, Dhaka HQ');
            $table->string('punch_source')->default('ZKTeco BioSync');
            $table->timestamps();
        });

        // 5. Payslips Table
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('employee_full_id');
            $table->string('employee_name');
            $table->string('month', 10); // e.g. 2026-09
            $table->string('designation');
            $table->string('department');
            $table->string('company')->default('Smart Technologies (BD) Ltd.');
            $table->decimal('basic_salary', 12, 2);
            $table->decimal('house_rent', 12, 2);
            $table->decimal('medical_allowance', 12, 2);
            $table->decimal('conveyance', 12, 2);
            $table->decimal('special_allowance', 12, 2)->default(0);
            $table->decimal('total_earnings', 12, 2);
            $table->decimal('pf_deduction', 12, 2)->default(0);
            $table->decimal('tax_deduction', 12, 2)->default(0);
            $table->decimal('loan_deduction', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2);
            $table->decimal('net_payable', 12, 2);
            $table->string('payment_method')->default('Bank Transfer');
            $table->date('payment_date')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('routing_no')->nullable();
            $table->string('status')->default('Paid');
            $table->timestamps();
        });

        // 6. Short Leaves Table
        Schema::create('short_leaves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('employee_name');
            $table->date('leave_day');
            $table->string('leave_type'); // early, delay
            $table->string('early_out_time')->nullable();
            $table->string('delay_in_time')->nullable();
            $table->text('reason');
            $table->string('emergency_phone')->nullable();
            $table->string('status')->default('Pending Recommend');
            $table->timestamps();
        });

        // 7. Late Requests (IOM) Table
        Schema::create('late_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('employee_name');
            $table->string('iom_type');
            $table->date('date');
            $table->string('in_time');
            $table->string('out_time')->nullable();
            $table->text('purpose');
            $table->string('status')->default('Pending');
            $table->timestamps();
        });

        // 8. Shift Exchanges Table
        Schema::create('shift_exchanges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('employee_name');
            $table->string('current_shift');
            $table->string('target_shift');
            $table->date('exchange_date');
            $table->text('description');
            $table->string('status')->default('Pending');
            $table->timestamps();
        });

        // 9. Outwork Applications Table
        Schema::create('outwork_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->date('date');
            $table->string('start_time');
            $table->string('return_time');
            $table->boolean('not_return')->default(false);
            $table->text('note');
            $table->string('status')->default('Pending Recommend');
            $table->timestamps();
        });

        // 10. Notices Table
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->date('publish_at');
            $table->date('expire_at');
            $table->string('department')->default('All Departments');
            $table->string('company')->default('Smart Technologies (BD) Ltd.');
            $table->string('priority')->default('Normal'); // High, Normal, Urgent
            $table->timestamps();
        });

        // 11. SND Customers Table
        Schema::create('snd_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('proprietor');
            $table->string('code')->unique();
            $table->string('route');
            $table->string('territory');
            $table->string('phone');
            $table->decimal('due_balance', 12, 2)->default(0);
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        // 12. SND Orders Table
        Schema::create('snd_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique();
            $table->unsignedBigInteger('customer_id');
            $table->string('customer_name');
            $table->date('date');
            $table->integer('item_count')->default(1);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('payable_amount', 12, 2);
            $table->string('payment_status')->default('Pending');
            $table->string('delivery_status')->default('Processing');
            $table->json('items_json')->nullable();
            $table->timestamps();
        });

        // 13. SND Visits Table
        Schema::create('snd_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('customer_name');
            $table->date('date');
            $table->string('time');
            $table->string('purpose');
            $table->string('outcome');
            $table->text('note')->nullable();
            $table->string('representative');
            $table->timestamps();
        });

        // 14. SFM Commitments Table
        Schema::create('sfm_commitments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('employee_name');
            $table->string('code');
            $table->string('territory');
            $table->string('month', 10);
            $table->decimal('target_value', 12, 2);
            $table->decimal('commitment_value', 12, 2);
            $table->decimal('actual_sales', 12, 2)->default(0);
            $table->decimal('pcmo_commitment', 12, 2)->default(0);
            $table->decimal('hddo_commitment', 12, 2)->default(0);
            $table->timestamps();
        });

        // 15. SFM Commitment Details Table
        Schema::create('sfm_commitment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commitment_id');
            $table->unsignedBigInteger('detail_id');
            $table->decimal('commitment_value', 12, 2);
            $table->timestamps();
        });

        // 16. Tour Plans Table
        Schema::create('tour_plans', function (Blueprint $table) {
            $table->id();
            $table->string('month', 10);
            $table->unsignedBigInteger('employee_id');
            $table->string('employee_name');
            $table->string('status')->default('Approved');
            $table->integer('total_working_days')->default(24);
            $table->integer('tour_days')->default(8);
            $table->string('base_station')->default('Dhaka Central Depot');
            $table->timestamps();
        });

        // 17. Tour Claims Table
        Schema::create('tour_claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_no')->unique();
            $table->string('month', 10);
            $table->date('date');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('employee_name');
            $table->string('route');
            $table->decimal('da_amount', 10, 2)->default(0);
            $table->decimal('ta_amount', 10, 2)->default(0);
            $table->decimal('hotel_amount', 10, 2)->default(0);
            $table->decimal('other_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('Approved');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_claims');
        Schema::dropIfExists('tour_plans');
        Schema::dropIfExists('sfm_commitment_details');
        Schema::dropIfExists('sfm_commitments');
        Schema::dropIfExists('snd_visits');
        Schema::dropIfExists('snd_orders');
        Schema::dropIfExists('snd_customers');
        Schema::dropIfExists('notices');
        Schema::dropIfExists('outwork_applications');
        Schema::dropIfExists('shift_exchanges');
        Schema::dropIfExists('late_requests');
        Schema::dropIfExists('short_leaves');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('leave_applications');
        Schema::dropIfExists('hr_loans');
        Schema::dropIfExists('employees');
    }
};
