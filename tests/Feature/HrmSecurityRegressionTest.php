<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HrmSecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function employee(string $id, int $company = 7, string $department = 'Administration'): User
    {
        $email = strtolower($id).'@example.test';
        DB::table('employees')->insert(['employee_id' => random_int(1, 1000000), 'employee_full_id' => $id, 'name' => $id, 'email' => $email, 'company_id' => $company, 'company' => 'Example Garments', 'designation' => 'Operator', 'department' => $department, 'status' => 'Active']);

        return User::factory()->create(['email' => $email]);
    }

    public function test_legacy_hr_routes_require_authentication_and_get_login_is_removed(): void
    {
        foreach (['user', 'dashboard', 'hrm/employees', 'hrm/profile', 'hrm/leave-applications', 'hrm/payslips', 'hrm/check-today-attendance'] as $route) {
            $this->getJson('/api/v1/'.$route)->assertUnauthorized();
        }
        $this->postJson('/api/change-password', ['old_password' => 'x', 'password' => 'password123', 'password_confirmation' => 'password123'])->assertUnauthorized();
        $this->get('/api/v1/dashboard')->assertUnauthorized();
        $this->getJson('/api/v1/login')->assertStatus(405);
        $this->postJson('/api/v1/register', [])->assertForbidden();
    }

    public function test_profile_directory_permissions_attendance_and_dashboard_are_scoped_and_honest(): void
    {
        $user = $this->employee('SELF');
        $this->employee('OTHER', 8);
        $this->actingAs($user, 'sanctum');
        $this->getJson('/api/v1/hrm/profile')->assertOk()->assertJsonPath('data.employee_full_id', 'SELF')->assertJsonPath('data.salary.basic', 0)->assertJsonPath('data.bank_informations.0.bank_account_no', null);
        $this->getJson('/api/v1/hrm/profile?employee_full_id=OTHER')->assertNotFound();
        $this->getJson('/api/v1/hrm/employees')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/hrm/permissions/employee/OTHER')->assertNotFound();
        $this->getJson('/api/v1/dashboard')->assertOk()->assertJsonPath('data.total_employees', 1)->assertJsonPath('data.present_today', 0)->assertJsonPath('data.pending_tasks.leave_approvals', 0)->assertJsonPath('data.weekly_attendance.0.present', 0);
        $this->getJson('/api/v1/hrm/v2/monthly-attendance-reports?month=2026-10')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/hrm/all-employees-today-attendance')->assertOk()->assertJsonPath('data.0.status', 'Absent');
        $this->postJson('/api/v1/hrm/punch-in', ['employee_full_id' => 'OTHER'])->assertForbidden();
        $this->postJson('/api/v1/hrm/profile-update', ['employee_full_id' => 'OTHER', 'present_address' => 'Own address'])->assertOk();
        $this->assertDatabaseHas('employees', ['employee_full_id' => 'SELF', 'present_address' => 'Own address']);
        $this->assertDatabaseMissing('employees', ['employee_full_id' => 'OTHER', 'present_address' => 'Own address']);
    }

    public function test_leave_uses_authenticated_identity_server_days_and_scoped_state_transitions(): void
    {
        $user = $this->employee('SELF');
        $other = $this->employee('OTHER', 8);
        $this->actingAs($user, 'sanctum');
        $body = ['leave_type_id' => 1, 'from_date' => '2026-10-08', 'to_date' => '2026-10-10', 'days_count' => 900, 'reason' => 'Personal', 'employee_full_id' => 'OTHER'];
        $id = $this->postJson('/api/v1/hrm/leave/apply', $body)->assertOk()->assertJsonPath('data.employee_full_id', 'SELF')->assertJsonPath('data.days_count', 3)->json('data.id');
        $this->postJson("/api/v1/hrm/approve-leave-application/$id")->assertStatus(409);
        $this->postJson("/api/v1/hrm/recommend-leave-application/$id")->assertOk();
        $this->postJson("/api/v1/hrm/approve-leave-application/$id")->assertOk()->assertJsonPath('data.approved_by', 'SELF');
        $this->postJson('/api/v1/hrm/leave/apply', array_replace($body, ['to_date' => '2026-10-01']))->assertUnprocessable();
        $this->actingAs($other, 'sanctum');
        $this->getJson('/api/v1/hrm/leave-applications')->assertJsonCount(0, 'data');
        $this->postJson("/api/v1/hrm/approve-leave-application/$id")->assertNotFound();
    }

    public function test_missing_payslip_is_not_fabricated_and_paid_snapshot_is_unchanged(): void
    {
        $this->actingAs($this->employee('SELF'), 'sanctum');
        $this->getJson('/api/v1/hrm/payslip?month=2026-10')->assertNotFound();
        DB::table('payslips')->insert(['employee_full_id' => 'SELF', 'employee_name' => 'SELF', 'month' => '2026-10', 'designation' => 'Operator', 'department' => 'Administration', 'basic_salary' => 100, 'house_rent' => 0, 'medical_allowance' => 0, 'conveyance' => 0, 'total_earnings' => 100, 'total_deductions' => 0, 'net_payable' => 100, 'status' => 'Paid']);
        $this->getJson('/api/v1/hrm/payslip?month=2026-10')->assertOk()->assertJsonPath('data.net_payable', 100);
        $this->getJson('/api/v1/hrm/payslips')->assertOk()->assertJsonPath('data.0.net_payable', 100);
        $id = DB::table('employees')->where('employee_full_id', 'SELF')->value('id');
        $this->postJson("/api/v1/hrm/employee-delete/$id")->assertOk();
        $this->assertDatabaseCount('payslips', 1);
        $this->getJson('/api/v1/hrm/profile')->assertForbidden();
    }

    public function test_verification_never_discloses_the_code_and_employee_creation_has_no_bank_default(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $this->actingAs($this->employee('SELF'), 'sanctum');
        $response = $this->postJson('/api/v1/auth/send-verification-code', ['email' => 'worker@example.test'])->assertOk();
        $this->assertArrayNotHasKey('dev_code', $response->json());
        $mail = \Illuminate\Support\Facades\Mail::sent(\App\Mail\EmployeeVerificationCode::class)->first();
        $token = $this->postJson('/api/v1/auth/verify-code', ['email' => 'worker@example.test', 'code' => $mail->code])->assertOk()->json('verification_token');
        $body = ['name' => 'New Worker', 'designation' => 'Operator', 'department' => 'Engineering', 'email' => 'worker@example.test', 'password' => 'long-worker-password', 'basic_salary' => 0, 'joining_date' => '2026-10-07', 'email_verification_token' => $token];
        $this->postJson('/api/v1/hrm/employee-create', $body)->assertCreated()->assertJsonPath('data.bank_account_no', null)->assertJsonPath('data.basic_salary', 0);
        $this->postJson('/api/v1/hrm/employee-create', array_replace($body, ['password' => '1234']))->assertUnprocessable();
    }

    public function test_logout_revokes_the_current_token_and_inactive_accounts_cannot_sign_in(): void
    {
        $user = $this->employee('SELF');
        $token = $user->createToken('test')->plainTextToken;
        $this->withToken($token)->postJson('/api/v1/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
        DB::table('employees')->where('employee_full_id', 'SELF')->update(['status' => 'Inactive']);
        $this->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'password'])->assertForbidden();
    }

    public function test_biometric_ingestion_does_not_claim_to_save_unimplemented_records(): void
    {
        $this->actingAs($this->employee('SELF'), 'sanctum');
        $this->postJson('/api/v1/push', ['data' => [['employee_full_id' => 'SELF']]])->assertStatus(501);
        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_directory_does_not_expose_salary_and_bank_data_to_ordinary_staff(): void
    {
        $user = $this->employee('STAFF', 7, 'Engineering');
        $this->employee('MANAGER');
        $this->actingAs($user, 'sanctum');
        $data = $this->getJson('/api/v1/hrm/employees')->assertOk()->json('data.0');
        $this->assertArrayNotHasKey('basic_salary', $data);
        $this->assertArrayNotHasKey('bank_account_no', $data);
        $profile = $this->getJson('/api/v1/hrm/profile?employee_full_id=MANAGER')->assertOk()->json('data');
        $this->assertArrayNotHasKey('salary', $profile);
        $this->assertArrayNotHasKey('bank_informations', $profile);
    }
    public function test_production_rejects_unscoped_legacy_reads_and_writes_on_both_api_prefixes(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $this->actingAs($this->employee('SELF'), 'sanctum');

        foreach (['/api/v1', '/api'] as $prefix) {
            foreach ([
                '/hrm/hr-loan-list',
                '/hrm/notice-list',
                '/hrm/short-leave/list',
                '/hrm/tour-plans',
                '/hrm/tranings',
                '/hrm/companies',
                '/accounting/summary',
                '/snd/dashboard',
                '/sfm/target-dashboard',
            ] as $route) {
                $this->getJson('https://localhost'.$prefix.$route)
                    ->assertStatus(503)
                    ->assertJsonPath('message', 'This legacy module requires company-scoped implementation before production use.');
            }
            $this->postJson('https://localhost'.$prefix.'/hrm/notice-create', ['title' => 'Should not save'])->assertStatus(503);
            $this->postJson('https://localhost'.$prefix.'/hrm/hr-loan-apply', ['amount' => 1000])->assertStatus(503);
        }

        $this->assertDatabaseCount('notices', 0);
        $this->assertDatabaseCount('hr_loans', 0);
    }

    public function test_production_keeps_company_scoped_workflows_available(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $this->actingAs($this->employee('SELF'), 'sanctum');

        foreach (['/api/v1', '/api'] as $prefix) {
            foreach (['/hrm/profile', '/hrm/employees', '/hrm/leave-applications', '/hrm/check-today-attendance', '/hrm/factory/operations', '/hrm/payroll/runs', '/hrm/people/overview'] as $route) {
                $this->getJson('https://localhost'.$prefix.$route)->assertOk();
            }
        }
    }

    public function test_legacy_previews_still_require_authentication_outside_production(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        $this->getJson('/api/v1/hrm/hr-loan-list')->assertUnauthorized();
        $this->actingAs($this->employee('SELF'), 'sanctum');
        $this->getJson('/api/v1/hrm/hr-loan-list')->assertOk();
    }

    public function test_production_rejects_non_delivery_mailers_without_creating_codes(): void
    {
        $this->actingAs($this->employee('SELF'), 'sanctum');
        $this->app->detectEnvironment(fn () => 'production');
        foreach (['log', 'array'] as $mailer) {
            config(['mail.default' => $mailer]);
            foreach (['/api/v1', '/api'] as $prefix) {
                $this->postJson('https://localhost'.$prefix.'/auth/send-verification-code', ['email' => 'new@example.test'])
                    ->assertStatus(503)
                    ->assertJsonPath('message', 'A delivery mailer must be configured.');
            }
        }
        $this->assertDatabaseCount('employee_email_verifications', 0);
    }
}
