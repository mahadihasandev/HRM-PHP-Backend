<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Central Permissions Catalog
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('module');
            $table->string('category')->default('navigation'); // navigation or action
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Employee Specific Permission Overrides
        Schema::create('employee_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('employee_full_id');
            $table->string('permission_key');
            $table->boolean('is_granted')->default(true);
            $table->string('granted_by')->nullable();
            $table->timestamps();

            $table->unique(['employee_full_id', 'permission_key']);
        });

        // Seed Default System Permissions
        $defaultPermissions = [
            ['key' => 'module.dashboard', 'name' => 'Executive Dashboard', 'module' => 'dashboard', 'category' => 'navigation', 'description' => 'View overall executive metrics and KPIs'],
            ['key' => 'module.employees', 'name' => 'Employee Directory', 'module' => 'employees', 'category' => 'navigation', 'description' => 'View staff directory and public profiles'],
            ['key' => 'module.attendance', 'name' => 'Attendance & Job Card', 'module' => 'attendance', 'category' => 'navigation', 'description' => 'View attendance logs, shifts, and punch records'],
            ['key' => 'module.leave', 'name' => 'Leave Management', 'module' => 'leave', 'category' => 'navigation', 'description' => 'View leave records, balances, and apply for leaves'],
            ['key' => 'module.salary', 'name' => 'Salary & Payroll', 'module' => 'salary', 'category' => 'navigation', 'description' => 'View payroll disbursements and salary structures'],
            ['key' => 'module.snd', 'name' => 'SND Distribution Network', 'module' => 'snd', 'category' => 'navigation', 'description' => 'Access dealer network, outlets, depots, and sales orders'],
            ['key' => 'module.sfm', 'name' => 'SFM Field Force Management', 'module' => 'sfm', 'category' => 'navigation', 'description' => 'Access SR quotas, tour plans, shop visits, and target commitments'],
            ['key' => 'module.requests', 'name' => 'Short Leave & IOM', 'module' => 'requests', 'category' => 'navigation', 'description' => 'Apply for short leaves, manual attendance requests, and IOM'],
            ['key' => 'module.shifts', 'name' => 'Shift Roster', 'module' => 'shifts', 'category' => 'navigation', 'description' => 'View shift assignments and roster exchanges'],
            ['key' => 'module.outwork', 'name' => 'Outwork & Tour Plans', 'module' => 'outwork', 'category' => 'navigation', 'description' => 'View and manage field tour plans and DA/TA claims'],
            ['key' => 'module.loans', 'name' => 'Loans & Advance', 'module' => 'loans', 'category' => 'navigation', 'description' => 'View company loan applications and repayment schedules'],
            ['key' => 'module.notices', 'name' => 'Circulars & Notices', 'module' => 'notices', 'category' => 'navigation', 'description' => 'View circulars, bulletins, and corporate notices'],
            ['key' => 'module.settings', 'name' => 'System & Device Settings', 'module' => 'settings', 'category' => 'navigation', 'description' => 'Configure biometric devices, tokens, and system parameters'],
            ['key' => 'action.employees.edit', 'name' => 'Edit Employee Record', 'module' => 'employees', 'category' => 'action', 'description' => 'Modify employee credentials, designation, and salary parameters'],
            ['key' => 'action.employees.delete', 'name' => 'Delete Employee Record', 'module' => 'employees', 'category' => 'action', 'description' => 'Permanently remove employee records from central database'],
            ['key' => 'action.permissions.manage', 'name' => 'Manage Permissions', 'module' => 'settings', 'category' => 'action', 'description' => 'Grant or revoke permissions for other employees'],
        ];

        foreach ($defaultPermissions as $perm) {
            DB::table('permissions')->insert(array_merge($perm, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_permissions');
        Schema::dropIfExists('permissions');
    }
};
