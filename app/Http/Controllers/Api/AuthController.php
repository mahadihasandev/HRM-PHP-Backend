<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends BaseApiController
{
    /**
     * Authenticate employee and issue token.
     */
    public function login(Request $request): JsonResponse
    {
        $identifier = trim((string) ($request->input('email') ?? $request->input('username') ?? $request->query('email') ?? ''));
        $password = (string) ($request->input('password') ?? $request->query('password') ?? '');

        if (!$identifier || !$password) {
            return $this->errorResponse('Email/Employee ID and Password are required', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Handle admin aliases
        if (in_array(strtolower($identifier), ['admin', 'admin@smart.com', 'admin@smarterp.biz'])) {
            $identifier = 'SMT-0001';
        }

        // Resolve employee record by employee_full_id, email, or employee_id
        $employeeQuery = DB::table('employees')
            ->where('employee_full_id', $identifier)
            ->orWhere('email', $identifier);

        if (is_numeric($identifier)) {
            $employeeQuery->orWhere('employee_id', (int) $identifier);
        }

        $employee = $employeeQuery->first();

        // Check against users table
        $user = User::where('email', $identifier)->first();
        if (!$user && $employee) {
            $user = User::where('email', $employee->email)
                ->orWhere('email', $employee->employee_full_id . '@smarterp.biz')
                ->first();
        }

        if (!$user && $identifier === 'SMT-0001') {
            $user = User::where('email', 'admin@smarterp.biz')
                ->orWhere('email', 'SMT-0001@smarterp.biz')
                ->first();
        }

        if ($user) {
            if (!Hash::check($password, $user->password)) {
                return $this->errorResponse('Invalid password provided', Response::HTTP_UNAUTHORIZED);
            }
        } else {
            return $this->errorResponse('Invalid credentials provided', Response::HTTP_UNAUTHORIZED);
        }

        if (!$employee || $employee->status !== 'Active') {
            return $this->errorResponse('An active employee account is required', 403);
        }

        $employeeName = $employee?->name ?? $user->name;
        $employeeFullId = $employee?->employee_full_id ?? ($identifier === 'admin@smart.com' ? 'SMT-0001' : $identifier);
        $employeeId = $employee?->id ?? ($employee?->employee_id ?? $user->id);

        $token = $user->createToken('hrm_api_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'token' => $token,
            'message' => 'Login successful',
            'company_id' => $employee?->company_id ?? 7,
            'user_id' => $user->id,
            'employee_id' => $employeeId,
            'employee_full_id' => $employeeFullId,
            'employee_name' => $employeeName,
            'name' => $employeeName,
            'email' => $employee?->email ?? $user->email,
            'department' => $employee?->department ?? 'General Operations',
            'designation' => $employee?->designation ?? 'Staff Member',
            'phone_number' => $employee?->phone ?? '01717186089',
            'is_active' => true,
        ]);
    }

    /**
     * Change authenticated user password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'old_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();
        if ($user && !Hash::check($request->old_password, $user->password)) {
            return $this->errorResponse('Old password does not match', Response::HTTP_BAD_REQUEST);
        }

        if ($user) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->tokens()->delete();
        return $this->successResponse(null, 'Password updated successfully. Please sign in again.');
    }

    /**
     * Get authenticated user profile.
     */
    public function user(Request $request): JsonResponse
    {
        $actor = app(\App\Services\Employees\EmployeeAccess::class)->actor($request);
        return $this->successResponse((array) $actor, 'User profile retrieved successfully');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();
        return $this->successResponse(null, 'Signed out successfully');
    }

    public function sendVerificationCode(\App\Http\Requests\SendEmployeeVerificationRequest $request, \App\Services\Employees\EmployeeEmailVerificationService $service): JsonResponse
    {
        return response()->json($service->send(new \App\DTOs\EmployeeEmailVerificationData((int) $request->user()->id, $request->validated('email'))));
    }

    public function verifyCode(\App\Http\Requests\VerifyEmployeeEmailRequest $request, \App\Services\Employees\EmployeeEmailVerificationService $service): JsonResponse
    {
        return response()->json($service->verify(new \App\DTOs\EmployeeEmailVerificationData((int) $request->user()->id, $request->validated('email'), $request->validated('code'))));
    }

    /**
     * Register a new User & Employee after email verification.
     */
    public function register(Request $request): JsonResponse
    {
        return $this->errorResponse('Accounts must be provisioned by your company HR administrator.', 403);
    }

}
