<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

    /**
     * Send a 6-digit email verification code.
     */
    public function sendVerificationCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:150',
        ]);

        abort_if(app()->isProduction() && in_array(config('mail.default'), ['log', 'array'], true), 503, 'A delivery mailer must be configured.');
        $email = strtolower(trim($validated['email']));
        DB::table('email_verifications')->where('email', $email)->delete();
        $code = sprintf('%06d', random_int(100000, 999999));
        $expiresAt = now()->addMinutes(15);

        // Record verification code in database
        DB::table('email_verifications')->insert([
            'email' => $email,
            'code' => $code,
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Attempt sending email via configured mailer if available
        try {
            \Illuminate\Support\Facades\Mail::raw("Your Smart HRM & ERP verification code is: {$code}. It expires in 15 minutes.", function ($message) use ($email) {
                $message->to($email)->subject('Smart HRM & ERP - Email Verification Code');
            });
        } catch (\Throwable $e) {
            Log::error('Verification email delivery failed.');
            DB::table('email_verifications')->where('email', $email)->where('code', $code)->delete();
            return $this->errorResponse('Verification email could not be sent. Please contact your administrator.', 503);
        }

        return response()->json([
            'status' => true,
            'message' => "Verification code sent to {$email}. Please check your inbox or notification.",
            'email' => $email,
            // Verification codes must never be returned to the browser.
            'expires_in_minutes' => 15,
        ]);
    }

    /**
     * Verify the 6-digit email code.
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:150',
            'code' => 'required|string|min:4|max:10',
        ]);

        $email = strtolower(trim($validated['email']));
        $code = trim((string) $validated['code']);

        $record = DB::table('email_verifications')
            ->where('email', $email)
            ->where('code', $code)
            ->where('expires_at', '>=', now())
            ->orderByDesc('id')
            ->first();

        if (!$record) {
            return response()->json([
                'status' => false,
                'verified' => false,
                'message' => 'Invalid or expired verification code. Please request a new code.',
            ], 422);
        }

        DB::table('email_verifications')
            ->where('id', $record->id)
            ->update([
                'verified_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'status' => true,
            'verified' => true,
            'message' => "Email {$email} verified successfully!",
        ]);
    }

    /**
     * Register a new User & Employee after email verification.
     */
    public function register(Request $request): JsonResponse
    {
        return $this->errorResponse('Accounts must be provisioned by your company HR administrator.', 403);
    }

}
