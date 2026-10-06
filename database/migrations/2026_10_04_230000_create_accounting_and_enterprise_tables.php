<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('vouchers')) {
            Schema::create('vouchers', function (Blueprint $table) {
                $table->id();
                $table->string('voucher_no')->unique();
                $table->date('date');
                $table->string('type', 10); // JV, BPV, CRV, CPV, BRV
                $table->string('account_code', 20);
                $table->string('account_name');
                $table->text('narration');
                $table->decimal('debit', 15, 2)->default(0);
                $table->decimal('credit', 15, 2)->default(0);
                $table->decimal('tax_tds_deduction', 15, 2)->default(0);
                $table->decimal('vat_vds_deduction', 15, 2)->default(0);
                $table->string('status', 20)->default('Posted');
                $table->string('created_by')->default('SMT-0026');
                $table->string('approved_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('chart_of_accounts')) {
            Schema::create('chart_of_accounts', function (Blueprint $table) {
                $table->id();
                $table->string('code', 20)->unique();
                $table->string('name');
                $table->string('bangla_name')->nullable();
                $table->string('category', 20); // Asset, Liability, Equity, Revenue, Expense
                $table->decimal('balance', 15, 2)->default(0);
                $table->string('parent_code', 20)->nullable();
                $table->string('status', 20)->default('Active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('daily_work_diaries')) {
            Schema::create('daily_work_diaries', function (Blueprint $table) {
                $table->id();
                $table->string('employee_full_id')->default('SMT-0026');
                $table->date('date');
                $table->text('description');
                $table->json('tasks')->nullable();
                $table->json('communications')->nullable();
                $table->json('appointments')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('trainings')) {
            Schema::create('trainings', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->date('start_date');
                $table->date('end_date');
                $table->string('venue');
                $table->string('duration');
                $table->string('trainer');
                $table->string('status', 20)->default('Upcoming');
                $table->integer('enrolled_count')->default(0);
                $table->text('message')->nullable();
                $table->timestamps();
            });
        }

        // Seed initial Accounting Data
        if (DB::table('chart_of_accounts')->count() === 0) {
            DB::table('chart_of_accounts')->insert([
                ['code' => '1010', 'name' => 'Cash in Hand & Petty Cash Float', 'bangla_name' => 'হাতে নগদ ও পেটি ক্যাশ', 'category' => 'Asset', 'balance' => 450000, 'created_at' => now(), 'updated_at' => now()],
                ['code' => '1020', 'name' => 'Cash at Bank - Prime Bank PLC (A/C 2104213044100)', 'bangla_name' => 'ব্যাংক হিসাব (প্রাইম ব্যাংক)', 'category' => 'Asset', 'balance' => 14200000, 'created_at' => now(), 'updated_at' => now()],
                ['code' => '1021', 'name' => 'Cash at Bank - BRAC Bank PLC (A/C 1501209876543)', 'bangla_name' => 'ব্যাংক হিসাব (ব্র্যাক ব্যাংক)', 'category' => 'Asset', 'balance' => 3800000, 'created_at' => now(), 'updated_at' => now()],
                ['code' => '1030', 'name' => 'Accounts Receivable - SND Dealers & Outlets', 'bangla_name' => 'প্রাপ্য হিসাব (ডিলার ও আউটলেট)', 'category' => 'Asset', 'balance' => 3820000, 'created_at' => now(), 'updated_at' => now()],
                ['code' => '1040', 'name' => 'Inventory - Finished Goods (PCMO & HDDO)', 'bangla_name' => 'মজুদ পণ্য (লুব্রিকেন্ট)', 'category' => 'Asset', 'balance' => 8950000, 'created_at' => now(), 'updated_at' => now()],
                ['code' => '2010', 'name' => 'Accounts Payable - Raw Material Suppliers', 'bangla_name' => 'প্রদেয় হিসাব (সরবরাহকারী)', 'category' => 'Liability', 'balance' => 2115000, 'created_at' => now(), 'updated_at' => now()],
                ['code' => '2020', 'name' => 'Salary & Allowances Payable', 'bangla_name' => 'প্রদেয় বেতন ও ভাতাদি', 'category' => 'Liability', 'balance' => 4850000, 'created_at' => now(), 'updated_at' => now()],
                ['code' => '2030', 'name' => 'NBR Withholding Tax (TDS) & VDS Payable', 'bangla_name' => 'জাতীয় রাজস্ব বোর্ড উৎসে কর ও ভ্যাট', 'category' => 'Liability', 'balance' => 865400, 'created_at' => now(), 'updated_at' => now()],
                ['code' => '2040', 'name' => 'Provident Fund (PF 8.33%) Contribution Payable', 'bangla_name' => 'ভবিষ্য তহবিল প্রদেয়', 'category' => 'Liability', 'balance' => 1240000, 'created_at' => now(), 'updated_at' => now()],
                ['code' => '3010', 'name' => 'Paid-up Share Capital', 'bangla_name' => 'পরিশোধিত মূলধন', 'category' => 'Equity', 'balance' => 15000000, 'created_at' => now(), 'updated_at' => now()],
                ['code' => '3020', 'name' => 'Retained Earnings & Reserves', 'bangla_name' => 'সংরক্ষিত আয় ও তহবিল', 'category' => 'Equity', 'balance' => 7449600, 'created_at' => now(), 'updated_at' => now()],
                ['code' => '4010', 'name' => 'Sales Revenue - SND Dealer Network', 'bangla_name' => 'পণ্য বিক্রয় আয় (ডিলার নেটওয়ার্ক)', 'category' => 'Revenue', 'balance' => 12450000, 'created_at' => now(), 'updated_at' => now()],
                ['code' => '4020', 'name' => 'Direct Corporate & Institutional Sales', 'bangla_name' => 'প্রাতিষ্ঠানিক বিক্রয় আয়', 'category' => 'Revenue', 'balance' => 2425000, 'created_at' => now(), 'updated_at' => now()],
                ['code' => '5010', 'name' => 'Employee Salaries, BLA Allowances & PF', 'bangla_name' => 'কর্মচারী বেতন, ভাতা ও পিএফ', 'category' => 'Expense', 'balance' => 5850000, 'created_at' => now(), 'updated_at' => now()],
                ['code' => '5020', 'name' => 'Office Rent, Utilities & Overheads', 'bangla_name' => 'অফিস ভাড়া ও ইউটিলিটি', 'category' => 'Expense', 'balance' => 1420000, 'created_at' => now(), 'updated_at' => now()],
                ['code' => '5030', 'name' => 'Field Force Tour DA/TA & Conveyance Claims', 'bangla_name' => 'ফিল্ড ফোর্স ট্যুর ও ভ্রমণ ভাতা', 'category' => 'Expense', 'balance' => 980000, 'created_at' => now(), 'updated_at' => now()],
                ['code' => '5040', 'name' => 'Vehicle Fuel, Maintenance & Logistics', 'bangla_name' => 'গাড়ি জ্বালানি ও মেরামত খরচ', 'category' => 'Expense', 'balance' => 990000, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (DB::table('vouchers')->count() === 0) {
            DB::table('vouchers')->insert([
                [
                    'voucher_no' => 'JV-2026-1001',
                    'date' => '2026-10-04',
                    'type' => 'JV',
                    'account_code' => '5010',
                    'account_name' => 'Employee Salaries, BLA Allowances & PF',
                    'narration' => 'Monthly payroll provision for September 2026 as per Bangladesh Labor Act 2006',
                    'debit' => 4850000,
                    'credit' => 0,
                    'tax_tds_deduction' => 0,
                    'vat_vds_deduction' => 0,
                    'status' => 'Posted',
                    'created_by' => 'SMT-0026',
                    'approved_by' => 'System Administrator',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'voucher_no' => 'BPV-2026-0842',
                    'date' => '2026-10-03',
                    'type' => 'BPV',
                    'account_code' => '1020',
                    'account_name' => 'Cash at Bank - Prime Bank PLC (A/C 2104213044100)',
                    'narration' => 'Disbursement of salary transfers via BEFTN/RTGS to corporate employees',
                    'debit' => 0,
                    'credit' => 4215000,
                    'tax_tds_deduction' => 385000,
                    'vat_vds_deduction' => 0,
                    'status' => 'Approved',
                    'created_by' => 'SMT-0026',
                    'approved_by' => 'Chief Financial Officer',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'voucher_no' => 'CRV-2026-0419',
                    'date' => '2026-10-02',
                    'type' => 'CRV',
                    'account_code' => '1030',
                    'account_name' => 'Accounts Receivable - SND Dealers & Outlets',
                    'narration' => 'Cash receipt from Mirpur Zone Distributors for Lubricant Sales Batch #902',
                    'debit' => 850000,
                    'credit' => 0,
                    'tax_tds_deduction' => 0,
                    'vat_vds_deduction' => 42500,
                    'status' => 'Posted',
                    'created_by' => 'SMT-0051',
                    'approved_by' => 'Accounts Manager',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'voucher_no' => 'CPV-2026-0312',
                    'date' => '2026-10-01',
                    'type' => 'CPV',
                    'account_code' => '5030',
                    'account_name' => 'Field Force Tour DA/TA & Conveyance Claims',
                    'narration' => 'DA/TA Reimbursement for Chittagong & Sylhet Regional Outlet Visits',
                    'debit' => 145000,
                    'credit' => 0,
                    'tax_tds_deduction' => 0,
                    'vat_vds_deduction' => 0,
                    'status' => 'Posted',
                    'created_by' => 'SMT-0007',
                    'approved_by' => 'SMT-0026',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        if (DB::table('trainings')->count() === 0) {
            DB::table('trainings')->insert([
                [
                    'id' => 110,
                    'title' => 'Advanced Field Sales Execution & Outlet Merchandising',
                    'start_date' => '2026-10-15',
                    'end_date' => '2026-10-18',
                    'venue' => 'Smart Training Institute, Level 5, Dhaka',
                    'duration' => '4 Days (24 Hours)',
                    'trainer' => 'Farhan Masud (Senior Sales Director)',
                    'status' => 'Upcoming',
                    'enrolled_count' => 28,
                    'message' => 'Mandatory training program for all Territory Officers and Field Sales Representatives.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 111,
                    'title' => 'Bangladesh Labor Act (BLA 2006) & NBR Tax TDS Regulations 2026',
                    'start_date' => '2026-10-22',
                    'end_date' => '2026-10-23',
                    'venue' => 'Corporate Auditorium & Virtual Hybrid',
                    'duration' => '2 Days (12 Hours)',
                    'trainer' => 'Advocate Kamrul Islam (Labor Law Specialist)',
                    'status' => 'Upcoming',
                    'enrolled_count' => 45,
                    'message' => 'Comprehensive workshop on statutory payroll compliance, gratuity, and tax withholding.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainings');
        Schema::dropIfExists('daily_work_diaries');
        Schema::dropIfExists('chart_of_accounts');
        Schema::dropIfExists('vouchers');
    }
};
