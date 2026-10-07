<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends BaseApiController
{
    /**
     * All system permissions with metadata.
     */
    public static array $systemPermissions = [
        // 1. Navigation Modules
        ['key' => 'module.dashboard', 'name' => 'Executive Dashboard', 'module' => 'dashboard', 'category' => 'navigation', 'description' => 'View overall executive metrics, KPIs, and corporate summaries'],
        ['key' => 'module.employees', 'name' => 'Employee Directory', 'module' => 'employees', 'category' => 'navigation', 'description' => 'Browse personnel records, designations, and public contact cards'],
        ['key' => 'module.attendance', 'name' => 'Attendance & Job Card', 'module' => 'attendance', 'category' => 'navigation', 'description' => 'Inspect employee monthly attendance, biometrics, and shift logs'],
        ['key' => 'module.leave', 'name' => 'Leave Management', 'module' => 'leave', 'category' => 'navigation', 'description' => 'View annual and medical leave balances under BLA 2006 compliance'],
        ['key' => 'module.factory', 'name' => 'Factory Operations', 'module' => 'factory', 'category' => 'navigation', 'description' => 'Factory units, lines, shifts, grades, production and safety records'],
        ['key' => 'module.salary', 'name' => 'Salary & Payroll', 'module' => 'salary', 'category' => 'navigation', 'description' => 'Inspect monthly payroll disbursements, basic pay, and payslips'],
        ['key' => 'module.snd', 'name' => 'SND Distribution Network', 'module' => 'snd', 'category' => 'navigation', 'description' => 'Access dealer network, retail outlets, primary sales orders, and depots'],
        ['key' => 'module.sfm', 'name' => 'SFM Field Force Management', 'module' => 'sfm', 'category' => 'navigation', 'description' => 'View field targets, monthly commitments, shop visits, and SR quotas'],
        ['key' => 'module.requests', 'name' => 'Short Leave & IOM', 'module' => 'requests', 'category' => 'navigation', 'description' => 'View manual punch requests, late entry explanations, and IOM movement'],
        ['key' => 'module.shifts', 'name' => 'Shift Roster', 'module' => 'shifts', 'category' => 'navigation', 'description' => 'View shift assignments, duty schedules, and roster exchanges'],
        ['key' => 'module.outwork', 'name' => 'Tour Plans & DA/TA Claims', 'module' => 'outwork', 'category' => 'navigation', 'description' => 'View official tour plans, travel authorizations, and daily allowances'],
        ['key' => 'module.loans', 'name' => 'Loans & Advance', 'module' => 'loans', 'category' => 'navigation', 'description' => 'Inspect provident fund loan applications and repayment status'],
        ['key' => 'module.notices', 'name' => 'Circulars & Notice Board', 'module' => 'notices', 'category' => 'navigation', 'description' => 'View company-wide circulars, administrative notices, and updates'],
        ['key' => 'module.settings', 'name' => 'System & Device Settings', 'module' => 'settings', 'category' => 'navigation', 'description' => 'Manage biometric hardware tokens, API credentials, and sync settings'],

        // 2. Action & Execution Privileges
        ['key' => 'action.employees.create', 'name' => 'Register New Employee', 'module' => 'employees', 'category' => 'action', 'description' => 'Create and onboard new employees into the central directory'],
        ['key' => 'action.employees.edit', 'name' => 'Edit Employee Record', 'module' => 'employees', 'category' => 'action', 'description' => 'Modify employee credentials, designation, and BLA salary structure'],
        ['key' => 'action.employees.delete', 'name' => 'Delete Employee Record', 'module' => 'employees', 'category' => 'action', 'description' => 'Permanently delete employee profiles and associated logs'],
        ['key' => 'action.attendance.punch', 'name' => 'Biometric & Mobile Punch', 'module' => 'attendance', 'category' => 'action', 'description' => 'Perform self punch-in and punch-out operations'],
        ['key' => 'action.leave.apply', 'name' => 'Apply for Leave', 'module' => 'leave', 'category' => 'action', 'description' => 'Submit leave requests to department supervisor'],
        ['key' => 'action.leave.approve', 'name' => 'Approve / Reject Leave', 'module' => 'leave', 'category' => 'action', 'description' => 'Recommend or sanction employee leave applications'],
        ['key' => 'action.salary.disburse', 'name' => 'Disburse Monthly Payroll', 'module' => 'salary', 'category' => 'action', 'description' => 'Execute bank transfers and process monthly salary statements'],
        ['key' => 'action.loans.apply', 'name' => 'Apply for Loans', 'module' => 'loans', 'category' => 'action', 'description' => 'Submit emergency or provident loan applications'],
        ['key' => 'action.loans.approve', 'name' => 'Sanction Loans & Advances', 'module' => 'loans', 'category' => 'action', 'description' => 'Approve employee loan requests and repayment terms'],
        ['key' => 'action.snd.orders', 'name' => 'Create SND Sales Order', 'module' => 'snd', 'category' => 'action', 'description' => 'Submit primary distributor sales orders to central depot'],
        ['key' => 'action.sfm.commitments', 'name' => 'Assign SR Targets', 'module' => 'sfm', 'category' => 'action', 'description' => 'Allocate monthly volume targets and revenue quotas to field sales staff'],
        ['key' => 'action.notices.publish', 'name' => 'Publish Corporate Notice', 'module' => 'notices', 'category' => 'action', 'description' => 'Post notices to digital bulletin board for all employees'],
        ['key' => 'action.permissions.manage', 'name' => 'Manage Access & Permissions', 'module' => 'settings', 'category' => 'action', 'description' => 'Grant or revoke access permissions for any user in the system'],
    ];

    /**
     * Default department access profiles.
     * Product Designers and Engineers do NOT see SND and SFM by default.
     */
    public static array $departmentProfiles = [
        'Engineering' => [
            'module.dashboard' => true,
            'module.employees' => true,
            'module.factory' => true,
            'module.attendance' => true,
            'module.leave' => true,
            'module.salary' => false,
            'module.snd' => false,
            'module.sfm' => false,
            'module.requests' => true,
            'module.shifts' => true,
            'module.outwork' => true,
            'module.loans' => true,
            'module.notices' => true,
            'module.settings' => false,
            'action.employees.create' => false,
            'action.employees.edit' => false,
            'action.employees.delete' => false,
            'action.attendance.punch' => true,
            'action.leave.apply' => true,
            'action.leave.approve' => false,
            'action.salary.disburse' => false,
            'action.loans.apply' => true,
            'action.loans.approve' => false,
            'action.snd.orders' => false,
            'action.sfm.commitments' => false,
            'action.notices.publish' => false,
            'action.permissions.manage' => false,
        ],
        'Product Design' => [
            'module.dashboard' => true,
            'module.employees' => true,
            'module.factory' => true,
            'module.attendance' => true,
            'module.leave' => true,
            'module.salary' => false,
            'module.snd' => false,
            'module.sfm' => false,
            'module.requests' => true,
            'module.shifts' => true,
            'module.outwork' => true,
            'module.loans' => true,
            'module.notices' => true,
            'module.settings' => false,
            'action.employees.create' => false,
            'action.employees.edit' => false,
            'action.employees.delete' => false,
            'action.attendance.punch' => true,
            'action.leave.apply' => true,
            'action.leave.approve' => false,
            'action.salary.disburse' => false,
            'action.loans.apply' => true,
            'action.loans.approve' => false,
            'action.snd.orders' => false,
            'action.sfm.commitments' => false,
            'action.notices.publish' => false,
            'action.permissions.manage' => false,
        ],
        'Sales & Distribution' => [
            'module.dashboard' => true,
            'module.employees' => true,
            'module.factory' => true,
            'module.attendance' => true,
            'module.leave' => true,
            'module.salary' => true,
            'module.snd' => true,
            'module.sfm' => true,
            'module.requests' => true,
            'module.shifts' => true,
            'module.outwork' => true,
            'module.loans' => true,
            'module.notices' => true,
            'module.settings' => false,
            'action.employees.create' => false,
            'action.employees.edit' => false,
            'action.employees.delete' => false,
            'action.attendance.punch' => true,
            'action.leave.apply' => true,
            'action.leave.approve' => false,
            'action.salary.disburse' => false,
            'action.loans.apply' => true,
            'action.loans.approve' => false,
            'action.snd.orders' => true,
            'action.sfm.commitments' => true,
            'action.notices.publish' => false,
            'action.permissions.manage' => false,
        ],
        'Human Resources' => [
            'module.dashboard' => true,
            'module.employees' => true,
            'module.factory' => true,
            'module.attendance' => true,
            'module.leave' => true,
            'module.salary' => true,
            'module.snd' => false,
            'module.sfm' => false,
            'module.requests' => true,
            'module.shifts' => true,
            'module.outwork' => true,
            'module.loans' => true,
            'module.notices' => true,
            'module.settings' => true,
            'action.employees.create' => true,
            'action.employees.edit' => true,
            'action.employees.delete' => true,
            'action.attendance.punch' => true,
            'action.leave.apply' => true,
            'action.leave.approve' => true,
            'action.salary.disburse' => true,
            'action.loans.apply' => true,
            'action.loans.approve' => true,
            'action.snd.orders' => false,
            'action.sfm.commitments' => false,
            'action.notices.publish' => true,
            'action.permissions.manage' => true,
        ],
        'Finance & Accounts' => [
            'module.dashboard' => true,
            'module.employees' => true,
            'module.factory' => true,
            'module.attendance' => true,
            'module.leave' => true,
            'module.salary' => true,
            'module.snd' => true,
            'module.sfm' => false,
            'module.requests' => true,
            'module.shifts' => false,
            'module.outwork' => true,
            'module.loans' => true,
            'module.notices' => true,
            'module.settings' => false,
            'action.employees.create' => false,
            'action.employees.edit' => false,
            'action.employees.delete' => false,
            'action.attendance.punch' => true,
            'action.leave.apply' => true,
            'action.leave.approve' => false,
            'action.salary.disburse' => true,
            'action.loans.apply' => true,
            'action.loans.approve' => true,
            'action.snd.orders' => true,
            'action.sfm.commitments' => false,
            'action.notices.publish' => false,
            'action.permissions.manage' => false,
        ],
        'Operations' => [
            'module.dashboard' => true,
            'module.employees' => true,
            'module.factory' => true,
            'module.attendance' => true,
            'module.leave' => true,
            'module.salary' => false,
            'module.snd' => true,
            'module.sfm' => true,
            'module.requests' => true,
            'module.shifts' => true,
            'module.outwork' => true,
            'module.loans' => true,
            'module.notices' => true,
            'module.settings' => false,
            'action.employees.create' => false,
            'action.employees.edit' => false,
            'action.employees.delete' => false,
            'action.attendance.punch' => true,
            'action.leave.apply' => true,
            'action.leave.approve' => false,
            'action.salary.disburse' => false,
            'action.loans.apply' => true,
            'action.loans.approve' => false,
            'action.snd.orders' => true,
            'action.sfm.commitments' => false,
            'action.notices.publish' => false,
            'action.permissions.manage' => false,
        ],
        'Supply Chain' => [
            'module.dashboard' => true,
            'module.employees' => true,
            'module.factory' => true,
            'module.attendance' => true,
            'module.leave' => true,
            'module.salary' => false,
            'module.snd' => true,
            'module.sfm' => false,
            'module.requests' => true,
            'module.shifts' => true,
            'module.outwork' => true,
            'module.loans' => true,
            'module.notices' => true,
            'module.settings' => false,
            'action.employees.create' => false,
            'action.employees.edit' => false,
            'action.employees.delete' => false,
            'action.attendance.punch' => true,
            'action.leave.apply' => true,
            'action.leave.approve' => false,
            'action.salary.disburse' => false,
            'action.loans.apply' => true,
            'action.loans.approve' => false,
            'action.snd.orders' => true,
            'action.sfm.commitments' => false,
            'action.notices.publish' => false,
            'action.permissions.manage' => false,
        ],
        'Legal & Compliance' => [
            'module.dashboard' => true,
            'module.employees' => true,
            'module.factory' => true,
            'module.attendance' => true,
            'module.leave' => true,
            'module.salary' => false,
            'module.snd' => false,
            'module.sfm' => false,
            'module.requests' => true,
            'module.shifts' => false,
            'module.outwork' => true,
            'module.loans' => true,
            'module.notices' => true,
            'module.settings' => false,
            'action.employees.create' => false,
            'action.employees.edit' => false,
            'action.employees.delete' => false,
            'action.attendance.punch' => true,
            'action.leave.apply' => true,
            'action.leave.approve' => false,
            'action.salary.disburse' => false,
            'action.loans.apply' => true,
            'action.loans.approve' => false,
            'action.snd.orders' => false,
            'action.sfm.commitments' => false,
            'action.notices.publish' => false,
            'action.permissions.manage' => false,
        ],
        'Administration' => [
            'module.dashboard' => true,
            'module.employees' => true,
            'module.factory' => true,
            'module.attendance' => true,
            'module.leave' => true,
            'module.salary' => true,
            'module.snd' => true,
            'module.sfm' => true,
            'module.requests' => true,
            'module.shifts' => true,
            'module.outwork' => true,
            'module.loans' => true,
            'module.notices' => true,
            'module.settings' => true,
            'action.employees.create' => true,
            'action.employees.edit' => true,
            'action.employees.delete' => true,
            'action.attendance.punch' => true,
            'action.leave.apply' => true,
            'action.leave.approve' => true,
            'action.salary.disburse' => true,
            'action.loans.apply' => true,
            'action.loans.approve' => true,
            'action.snd.orders' => true,
            'action.sfm.commitments' => true,
            'action.notices.publish' => true,
            'action.permissions.manage' => true,
        ],
    ];

    /**
     * Ensure all catalog permissions exist in database.
     */
    protected function syncCatalog(): void
    {
        foreach (self::$systemPermissions as $perm) {
            DB::table('permissions')->updateOrInsert(
                ['key' => $perm['key']],
                array_merge($perm, ['updated_at' => now(), 'created_at' => now()])
            );
        }
    }

    /**
     * List all available permissions catalog.
     */
    public function index(): JsonResponse
    {
        $this->syncCatalog();

        $permissions = DB::table('permissions')->orderBy('category')->orderBy('module')->get();

        return response()->json([
            'status' => true,
            'data' => $permissions,
            'department_profiles' => self::$departmentProfiles,
        ]);
    }

    /**
     * Resolve employee from id, code, or email.
     */
    protected function findEmployee(int|string $id): ?object
    {
        $idStr = trim((string) $id);
        if (in_array(strtolower($idStr), ['admin@smart.com', 'admin@smarterp.biz', 'admin'])) {
            $idStr = 'SMT-0001';
        }

        $actor = request()->attributes->get('employee_actor');
        return DB::table('employees')->where('company_id', $actor->company_id)->where(function ($query) use ($idStr) {
            $query->where('employee_full_id', $idStr)->orWhere('email', $idStr);
            if (is_numeric($idStr)) $query->orWhere('id', (int) $idStr)->orWhere('employee_id', (int) $idStr);
        })->first();
    }

    /**
     * Get effective permissions for a specific employee.
     */
    public function getEmployeePermissions(Request $request, int|string $id): JsonResponse
    {
        $this->syncCatalog();

        $actor = $request->attributes->get('employee_actor');
        abort_unless((string) $id === (string) $actor->id || (string) $id === $actor->employee_full_id || app(\App\Services\Employees\EmployeeAccess::class)->allowed($actor, 'action.permissions.manage'), 403);
        $employee = $this->findEmployee($id);

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => "Employee '{$id}' not found.",
            ], 404);
        }

        $allPermissions = DB::table('permissions')->get();

        // 1. Determine department baseline defaults
        $deptDefaults = self::$departmentProfiles[$employee->department]
            ?? self::$departmentProfiles['Engineering'];

        // 2. Fetch specific overrides stored in employee_permissions
        $overrides = DB::table('employee_permissions')
            ->where('employee_full_id', $employee->employee_full_id)
            ->orWhere('employee_id', $employee->id)
            ->pluck('is_granted', 'permission_key')
            ->toArray();

        // Convert 1/0 or strings to boolean
        $overridesBool = [];
        foreach ($overrides as $k => $v) {
            $overridesBool[$k] = (bool) $v;
        }

        // 3. Compute effective permissions (Department defaults overridden by explicit employee settings)
        $effective = [];
        foreach ($allPermissions as $perm) {
            $key = $perm->key;
            if (array_key_exists($key, $overridesBool)) {
                $effective[$key] = $overridesBool[$key];
            } elseif (array_key_exists($key, $deptDefaults)) {
                $effective[$key] = $deptDefaults[$key];
            } else {
                $effective[$key] = false;
            }
        }

        return response()->json([
            'status' => true,
            'employee' => [
                'id' => $employee->id,
                'employee_full_id' => $employee->employee_full_id,
                'name' => $employee->name,
                'designation' => $employee->designation,
                'department' => $employee->department,
                'company' => $employee->company,
                'status' => $employee->status,
            ],
            'department_defaults' => $deptDefaults,
            'custom_overrides' => $overridesBool,
            'effective_permissions' => $effective,
        ]);
    }

    /**
     * Update/Assign permissions for an employee.
     */
    public function updateEmployeePermissions(Request $request, int|string $id): JsonResponse
    {
        $this->syncCatalog();

        // Security Policy: Only Admin-level accounts can grant or revoke permissions
        if (!$this->authorizeAdmin($request)) {
            return response()->json([
                'status' => false,
                'message' => 'Access Denied: Only Admin-level accounts (Administration Department / SMT-0001) have authorization to grant, revoke, or modify employee permissions.',
                'required_permission' => 'action.permissions.manage',
            ], 403);
        }

        $employee = $this->findEmployee($id);

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => "Employee '{$id}' not found.",
            ], 404);
        }

        // Accept either a map {"module.snd": true, "module.sfm": true} or single {permission_key: "...", is_granted: true}
        $permissions = $request->input('permissions');
        if (!is_array($permissions)) {
            $key = $request->input('permission_key');
            $val = $request->input('is_granted', true);
            if ($key) {
                $permissions = [$key => (bool) $val];
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No permissions specified in request payload.',
                ], 422);
            }
        }

        foreach ($permissions as $permKey => $isGranted) {
            DB::table('employee_permissions')->updateOrInsert(
                [
                    'employee_full_id' => $employee->employee_full_id,
                    'permission_key' => $permKey,
                ],
                [
                    'employee_id' => $employee->id,
                    'is_granted' => (bool) $isGranted,
                    'granted_by' => $request->attributes->get('employee_actor')->name,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        // Return updated result
        return $this->getEmployeePermissions($request, (string) $employee->id);
    }

    /**
     * Check if a specific employee or active user has a given permission.
     */
    public function check(Request $request): JsonResponse
    {
        $employeeId = $request->input('employee_id') ?? $request->query('employee_id') ?? $request->attributes->get('employee_actor')->employee_full_id;
        $permissionKey = $request->input('permission') ?? $request->query('permission');

        if (!$permissionKey) {
            return response()->json([
                'status' => false,
                'message' => 'Permission key is required.',
            ], 422);
        }

        $res = $this->getEmployeePermissions($request, (string) $employeeId);
        $data = $res->getData(true);

        if (!($data['status'] ?? false)) {
            return $res;
        }

        $isAllowed = $data['effective_permissions'][$permissionKey] ?? false;

        return response()->json([
            'status' => true,
            'employee_full_id' => $data['employee']['employee_full_id'],
            'permission' => $permissionKey,
            'allowed' => (bool) $isAllowed,
        ]);
    }

    /**
     * Grant ALL permissions to an employee with a single call.
     */
    public function grantAll(Request $request, int|string $id): JsonResponse
    {
        $this->syncCatalog();
        $allPermissions = DB::table('permissions')->pluck('key')->toArray();
        $permMap = array_fill_keys($allPermissions, true);

        $request->merge(['permissions' => $permMap]);
        return $this->updateEmployeePermissions($request, $id);
    }

    /**
     * Revoke ALL permissions from an employee with a single call (cannot see or do anything).
     */
    public function revokeAll(Request $request, int|string $id): JsonResponse
    {
        $this->syncCatalog();
        $allPermissions = DB::table('permissions')->pluck('key')->toArray();
        $permMap = array_fill_keys($allPermissions, false);

        $request->merge(['permissions' => $permMap]);
        return $this->updateEmployeePermissions($request, $id);
    }

    /**
     * Reset employee permissions back to department defaults (remove custom overrides).
     */
    public function resetDefaults(Request $request, int|string $id): JsonResponse
    {
        // Security Policy: Only Admin-level accounts can reset permissions
        if (!$this->authorizeAdmin($request)) {
            return response()->json([
                'status' => false,
                'message' => 'Access Denied: Only Admin-level accounts have authorization to reset employee permissions.',
                'required_permission' => 'action.permissions.manage',
            ], 403);
        }

        $employee = $this->findEmployee($id);

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => "Employee '{$id}' not found.",
            ], 404);
        }

        DB::table('employee_permissions')
            ->where('employee_full_id', $employee->employee_full_id)
            ->orWhere('employee_id', $employee->id)
            ->delete();

        return $this->getEmployeePermissions($request, (string) $employee->id);
    }

    /**
     * Authorize that the requester has administrative privileges.
     * Only Admin-level accounts (Administration Department / SMT-0001) can grant or modify permissions.
     */
    protected function authorizeAdmin(Request $request): bool
    {
        $user = $request->user();
        if (!$user) { return false; }
        $repository = app(\App\Repositories\Contracts\PayrollRepositoryInterface::class);
        $actor = $repository->actor($user);
        return $actor && $actor->status === 'Active' && $repository->allowed($actor, 'action.permissions.manage');
    }
}
