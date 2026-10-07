<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Services\Employees\EmployeeAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends BaseApiController
{
    /**
     * Get detailed profile of the authenticated employee.
     */
    public function profile(Request $request): JsonResponse
    {
        $emp = app(EmployeeAccess::class)->target($request, 'module.employees');
        $actor = app(EmployeeAccess::class)->actor($request);
        if ($emp->id !== $actor->id && ! app(EmployeeAccess::class)->allowed($actor, 'module.salary') && ! app(EmployeeAccess::class)->allowed($actor, 'action.employees.edit')) {
            return $this->successResponse(array_intersect_key((array) $emp, array_flip(['id', 'employee_id', 'employee_full_id', 'name', 'email', 'phone', 'designation', 'department', 'company', 'status'])));
        }
        $data = (array) $emp;
        $data['personal_phone_number'] = $emp->personal_phone ?? $emp->phone ?? '';
        $data['salary'] = ['basic' => (float) $emp->basic_salary, 'house_rent' => (float) $emp->house_rent,
            'medical_allowance' => (float) $emp->medical_allowance, 'conveyance' => (float) $emp->conveyance,
            'gross' => (float) $emp->gross_salary, 'pf_deduction' => (float) $emp->pf_deduction,
            'tax_deduction' => (float) $emp->tax_deduction, 'net_payable' => (float) $emp->net_payable];
        $data['bank_informations'] = [['bank_name' => $emp->bank_name, 'branch_name' => $emp->branch_name,
            'bank_account_no' => $emp->bank_account_no, 'routing_name' => $emp->routing_name]];

        return $this->successResponse($data, 'Profile retrieved successfully');
    }

    /**
     * Update employee personal and educational details.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $actor = app(EmployeeAccess::class)->actor($request);
        $values = $request->validate(['personal_phone_number' => 'sometimes|nullable|string|max:50', 'present_address' => 'sometimes|nullable|string|max:255']);
        if (array_key_exists('personal_phone_number', $values)) {
            $values['personal_phone'] = $values['personal_phone_number'];
            unset($values['personal_phone_number']);
        }
        DB::table('employees')->where('id', $actor->id)->update($values + ['updated_at' => now()]);

        return $this->successResponse($values, 'Profile information updated successfully');
    }

    /**
     * Get list of employees with search and department filtering.
     */
    public function getEmployees(Request $request): JsonResponse
    {
        $search = $request->input('search');

        $actor = app(EmployeeAccess::class)->actor($request);
        $access = app(EmployeeAccess::class);
        abort_unless($access->allowed($actor, 'module.employees'), 403, 'Employee directory permission is required.');
        $query = DB::table('employees')->where('company_id', $actor->company_id);
        if (! $access->allowed($actor, 'module.salary') && ! $access->allowed($actor, 'action.employees.edit')) {
            $query->select(['id', 'employee_id', 'employee_full_id', 'name', 'email', 'phone', 'designation', 'department', 'company', 'status']);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_full_id', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        $employees = $query->orderBy('id')->get();

        return response()->json([
            'status' => true,
            'data' => $employees,
            'total' => $employees->count(),
        ]);
    }

    /**
     * Get company-wise users.
     */
    public function companyWiseUsers(Request $request): JsonResponse
    {
        return $this->getEmployees($request);
    }

    /**
     * Create/Register a new employee into central database.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'designation' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'email' => 'nullable|email|max:150|unique:employees,email|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|string|min:8',
            'basic_salary' => 'required|numeric|min:0|max:999999999.99',
            'bank_account_no' => ['nullable', 'string', 'regex:/^[0-9]{5,34}$/'],
            'joining_date' => 'required|date',
        ]);

        return DB::transaction(function () use ($request, $validated): JsonResponse {
            $nextId = ((int) (DB::table('employees')->max('id') ?? 65)) + 1;
            $fullId = sprintf('SMT-%04d', $nextId);

            $basicSalary = (float) $request->input('basic_salary', 45000);
            $houseRent = round($basicSalary * 0.50, 2);
            $medical = round($basicSalary * 0.10, 2);
            $conveyance = 4000.00;
            $gross = $basicSalary + $houseRent + $medical + $conveyance;
            $pf = round($basicSalary * 0.0833, 2);
            $tax = round($gross > 50000 ? ($gross - 50000) * 0.10 : 0, 2);
            $netPayable = $gross - $pf - $tax;

            $rawEmail = $request->input('email');
            if (! $rawEmail) {
                $baseSlug = strtolower((string) preg_replace('/[^a-z0-9]/i', '', $validated['name']));
                $rawEmail = ($baseSlug ?: strtolower(str_replace('-', '', $fullId))).'@smarterp.biz';
            }

            // Avoid duplicate email collisions
            if (DB::table('employees')->where('email', $rawEmail)->exists()) {
                $rawEmail = strtolower(str_replace('-', '', $fullId)).'@smarterp.biz';
            }

            $empData = [
                'id' => $nextId,
                'employee_id' => $nextId,
                'employee_full_id' => $fullId,
                'name' => $validated['name'],
                'email' => $rawEmail,
                'phone' => $request->input('phone', '01712000000'),
                'personal_phone' => $request->input('phone', '01712000000'),
                'designation' => $validated['designation'],
                'department' => $validated['department'],
                'company' => $request->attributes->get('employee_actor')?->company ?? 'Smart Technologies (BD) Ltd.',
                'company_id' => $request->attributes->get('employee_actor')?->company_id ?? 7,
                'status' => 'Active',
                'blood_group' => $request->input('blood_group', 'B+'),
                'gender' => $request->input('gender', 'Male'),
                'marital_status' => $request->input('marital_status', 'Married'),
                'religion' => $request->input('religion', 'Islam'),
                'date_of_birth' => $request->input('date_of_birth', '1995-01-01'),
                'joining_date' => $request->input('joining_date', now()->toDateString()),
                'present_address' => $request->input('present_address', 'Dhaka, Bangladesh'),
                'permanent_address' => $request->input('permanent_address', 'Bangladesh'),
                'bank_name' => $request->input('bank_name'),
                'bank_account_no' => $request->input('bank_account_no'),
                'basic_salary' => $basicSalary,
                'house_rent' => $houseRent,
                'medical_allowance' => $medical,
                'conveyance' => $conveyance,
                'gross_salary' => $gross,
                'pf_deduction' => $pf,
                'tax_deduction' => $tax,
                'net_payable' => $netPayable,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('employees')->insert($empData);

            // Provision User account immediately so the employee is a user and can log in & punch
            $userPassword = $validated['password'];
            User::create(
                [
                    'email' => $rawEmail,
                    'name' => $validated['name'],
                    'password' => Hash::make($userPassword),
                    'email_verified_at' => now(),
                ]
            );

            $verificationCode = $request->input('verification_code');
            if ($verificationCode) {
                DB::table('email_verifications')
                    ->where('email', strtolower(trim($rawEmail)))
                    ->where('code', trim((string) $verificationCode))
                    ->update(['verified_at' => now(), 'updated_at' => now()]);
            }

            return response()->json([
                'status' => true,
                'message' => "Employee {$validated['name']} registered successfully with ID {$fullId}. User account provisioned.",
                'data' => array_merge($empData, [
                    'can_login' => true,

                ]),
            ], 201);
        });
    }

    /**
     * Update an existing employee.
     */
    public function update(Request $request, int|string $id): JsonResponse
    {
        $employee = DB::table('employees')->where('company_id', $request->attributes->get('employee_actor')->company_id)
            ->where(function ($query) use ($id) {
                $query->where('employee_full_id', (string) $id);
                if (is_numeric($id)) {
                    $query->orWhere('id', (int) $id)->orWhere('employee_id', (int) $id);
                }
            })->first();

        if (! $employee) {
            return response()->json([
                'status' => false,
                'message' => "Employee with identifier '{$id}' not found.",
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'designation' => 'sometimes|required|string|max:100',
            'department' => 'sometimes|required|string|max:100',
            'email' => ['sometimes', 'required', 'email', 'max:150', Rule::unique('employees', 'email')->ignore($employee->id), Rule::unique('users', 'email')->ignore(User::where('email', $employee->email)->value('id'))],
            'phone' => 'nullable|string|max:50',
            'personal_phone' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:Active,Inactive,On Leave',
            'blood_group' => 'nullable|string|max:10',
            'gender' => 'nullable|string|max:20',
            'marital_status' => 'nullable|string|max:20',
            'religion' => 'nullable|string|max:30',
            'date_of_birth' => 'nullable|string|max:30',
            'joining_date' => 'nullable|string|max:30',
            'present_address' => 'nullable|string|max:255',
            'permanent_address' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:150',
            'mother_name' => 'nullable|string|max:150',
            'bank_name' => 'nullable|string|max:150',
            'bank_account_no' => ['nullable', 'string', 'regex:/^[0-9]{5,34}$/'],
            'basic_salary' => 'nullable|numeric|min:0',
        ]);

        $updateData = [];

        if ($request->has('name')) {
            $updateData['name'] = $request->input('name');
        }
        if ($request->has('designation')) {
            $updateData['designation'] = $request->input('designation');
        }
        if ($request->has('department')) {
            $updateData['department'] = $request->input('department');
        }
        if ($request->has('email')) {
            $updateData['email'] = $request->input('email');
        }
        if ($request->has('phone')) {
            $updateData['phone'] = $request->input('phone');
            if (! $request->has('personal_phone')) {
                $updateData['personal_phone'] = $request->input('phone');
            }
        }
        if ($request->has('personal_phone')) {
            $updateData['personal_phone'] = $request->input('personal_phone');
        }
        if ($request->has('status')) {
            $updateData['status'] = $request->input('status');
        }
        if ($request->has('blood_group')) {
            $updateData['blood_group'] = $request->input('blood_group');
        }
        if ($request->has('gender')) {
            $updateData['gender'] = $request->input('gender');
        }
        if ($request->has('marital_status')) {
            $updateData['marital_status'] = $request->input('marital_status');
        }
        if ($request->has('religion')) {
            $updateData['religion'] = $request->input('religion');
        }
        if ($request->has('date_of_birth')) {
            $updateData['date_of_birth'] = $request->input('date_of_birth');
        }
        if ($request->has('joining_date')) {
            $updateData['joining_date'] = $request->input('joining_date');
        }
        if ($request->has('present_address')) {
            $updateData['present_address'] = $request->input('present_address');
        }
        if ($request->has('permanent_address')) {
            $updateData['permanent_address'] = $request->input('permanent_address');
        }
        if ($request->has('father_name')) {
            $updateData['father_name'] = $request->input('father_name');
        }
        if ($request->has('mother_name')) {
            $updateData['mother_name'] = $request->input('mother_name');
        }
        if ($request->has('bank_name')) {
            $updateData['bank_name'] = $request->input('bank_name');
        }
        if ($request->has('bank_account_no')) {
            $updateData['bank_account_no'] = $request->input('bank_account_no');
        }

        // If basic_salary is updated, recalculate statutory values
        if ($request->has('basic_salary') && $request->input('basic_salary') !== null) {
            $basicSalary = (float) $request->input('basic_salary');
            $houseRent = round($basicSalary * 0.50, 2);
            $medical = round($basicSalary * 0.10, 2);
            $conveyance = 4000.00;
            $gross = $basicSalary + $houseRent + $medical + $conveyance;
            $pf = round($basicSalary * 0.0833, 2);
            $tax = round($gross > 50000 ? ($gross - 50000) * 0.10 : 0, 2);
            $netPayable = $gross - $pf - $tax;

            $updateData['basic_salary'] = $basicSalary;
            $updateData['house_rent'] = $houseRent;
            $updateData['medical_allowance'] = $medical;
            $updateData['conveyance'] = $conveyance;
            $updateData['gross_salary'] = $gross;
            $updateData['pf_deduction'] = $pf;
            $updateData['tax_deduction'] = $tax;
            $updateData['net_payable'] = $netPayable;
        }

        $updateData['updated_at'] = now();

        DB::table('employees')->where('id', $employee->id)->update($updateData);

        // Keep User account in sync if name or email changed
        if (isset($updateData['email']) || isset($updateData['name'])) {
            $newEmail = $updateData['email'] ?? $employee->email;
            $newName = $updateData['name'] ?? $employee->name;
            User::where('email', $employee->email)->update([
                'name' => $newName,
                'email' => $newEmail,
            ]);
        }

        $updatedEmployee = DB::table('employees')->where('id', $employee->id)->first();

        return response()->json([
            'status' => true,
            'message' => "Employee {$updatedEmployee->name} updated successfully",
            'data' => $updatedEmployee,
        ]);
    }

    /**
     * Delete an employee from central database.
     */
    public function destroy(Request $request, int|string $id): JsonResponse
    {
        $employee = DB::table('employees')->where('company_id', $request->attributes->get('employee_actor')->company_id)
            ->where(function ($query) use ($id) {
                $query->where('employee_full_id', (string) $id);
                if (is_numeric($id)) {
                    $query->orWhere('id', (int) $id)->orWhere('employee_id', (int) $id);
                }
            })->first();

        if (! $employee) {
            return response()->json([
                'status' => false,
                'message' => "Employee with identifier '{$id}' not found.",
            ], 404);
        }

        DB::transaction(function () use ($employee): void {
            DB::table('employees')->where('id', $employee->id)->update(['status' => 'Inactive', 'updated_at' => now()]);
            $user = User::where('email', $employee->email)->first();
            $user?->tokens()->delete();
        });

        return response()->json([
            'status' => true,
            'message' => "Employee {$employee->name} ({$employee->employee_full_id}) deactivated successfully; payroll history retained",
            'deleted_id' => $employee->id,
        ]);
    }
}
