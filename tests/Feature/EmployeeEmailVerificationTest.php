<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\EmployeeVerificationCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmployeeEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function hr(string $id = 'HR', string $department = 'Administration'): User
    {
        $email = strtolower($id).'@example.test';
        DB::table('employees')->insert(['employee_id' => random_int(1, 1000000), 'employee_full_id' => $id, 'name' => $id, 'email' => $email, 'company_id' => 7, 'company' => 'Example Garments', 'designation' => 'Officer', 'department' => $department, 'status' => 'Active']);
        $user = User::factory()->create(['email' => $email]);
        $this->actingAs($user, 'sanctum');
        return $user;
    }

    private function payload(array $overrides = []): array
    {
        return array_replace(['name' => 'New Worker', 'designation' => 'Operator', 'department' => 'Engineering', 'email' => 'worker@example.test', 'password' => 'long-worker-password', 'basic_salary' => 0, 'joining_date' => '2026-10-13'], $overrides);
    }

    private function sendCode(string $email = 'worker@example.test'): string
    {
        $response = $this->postJson('/api/v1/auth/send-verification-code', ['email' => $email])->assertOk();
        $this->assertArrayNotHasKey('dev_code', $response->json());
        $this->assertArrayNotHasKey('code', $response->json());
        $mail = Mail::sent(EmployeeVerificationCode::class)->last();
        $this->assertTrue($mail->hasTo(strtolower(trim($email))));
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $mail->code);
        $record = DB::table('employee_email_verifications')->where('email', strtolower(trim($email)))->first();
        $this->assertTrue(Hash::check($mail->code, $record->code_hash));
        return $mail->code;
    }

    private function verify(string $code, string $email = 'worker@example.test'): string
    {
        return $this->postJson('/api/v1/auth/verify-code', ['email' => $email, 'code' => $code])->assertOk()->assertJsonPath('verified', true)->json('verification_token');
    }

    public function test_only_authorized_hr_can_send_and_verify_codes(): void
    {
        Mail::fake();
        foreach (['/api', '/api/v1'] as $prefix) {
            $this->postJson($prefix.'/auth/send-verification-code', ['email' => 'worker@example.test'])->assertUnauthorized();
            $this->postJson($prefix.'/auth/verify-code', ['email' => 'worker@example.test', 'code' => '123456'])->assertUnauthorized();
        }
        $this->hr('STAFF', 'Engineering');
        $this->postJson('/api/v1/auth/send-verification-code', ['email' => 'worker@example.test'])->assertForbidden();
        Mail::assertNothingSent();
    }

    public function test_creation_requires_real_email_and_server_proof_on_both_routes(): void
    {
        $this->hr();
        foreach (['/api/v1/hrm/employee-create', '/api/v1/hrm/employees', '/api/hrm/employee-create'] as $route) {
            $this->postJson($route, $this->payload(['email' => null]))->assertUnprocessable()->assertJsonValidationErrors('email');
            $this->postJson($route, $this->payload(['is_verified' => true, 'verification_code' => '123456']))->assertUnprocessable();
            $this->postJson($route, $this->payload(['email_verification_token' => str_repeat('a', 64)]))->assertUnprocessable();
        }
        $this->assertDatabaseCount('employees', 1);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_delivered_code_verifies_email_and_proof_is_consumed_on_creation(): void
    {
        Mail::fake();
        $this->hr();
        $code = $this->sendCode(' Worker@Example.test ');
        $token = $this->verify($code);
        $this->postJson('/api/v1/hrm/employee-create', $this->payload(['email_verification_token' => $token]))
            ->assertCreated()->assertJsonPath('data.bank_account_no', null)->assertJsonPath('data.email', 'worker@example.test');
        $this->assertNotNull(User::where('email', 'worker@example.test')->first()->email_verified_at);
        $this->assertDatabaseCount('employee_email_verifications', 0);
        $this->postJson('/api/v1/hrm/employee-create', $this->payload(['email' => 'different@example.test', 'email_verification_token' => $token]))->assertUnprocessable();
    }

    public function test_proof_is_bound_to_requester_and_email(): void
    {
        Mail::fake();
        $owner = $this->hr();
        $code = $this->sendCode();
        $this->hr('OTHER');
        $this->postJson('/api/v1/auth/verify-code', ['email' => 'worker@example.test', 'code' => $code])->assertUnprocessable();
        $this->actingAs($owner, 'sanctum');
        $token = $this->verify($code);
        $this->postJson('/api/v1/hrm/employees', $this->payload(['email' => 'different@example.test', 'email_verification_token' => $token]))->assertUnprocessable();
        $this->hr('THIRD');
        $this->postJson('/api/v1/hrm/employees', $this->payload(['email_verification_token' => $token]))->assertUnprocessable();
    }

    public function test_five_wrong_attempts_lock_the_code_even_when_the_right_code_is_supplied(): void
    {
        Mail::fake();
        $this->hr();
        $code = $this->sendCode();
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/verify-code', ['email' => 'worker@example.test', 'code' => $code === '111111' ? '222222' : '111111'])->assertUnprocessable();
            $this->travel(61)->seconds();
        }
        $this->postJson('/api/v1/auth/verify-code', ['email' => 'worker@example.test', 'code' => $code])->assertUnprocessable();
        $this->assertDatabaseHas('employee_email_verifications', ['attempts' => 5]);
    }

    public function test_resend_has_cooldown_and_invalidates_old_proof(): void
    {
        Mail::fake();
        $this->hr();
        $token = $this->verify($this->sendCode());
        $this->postJson('/api/v1/auth/send-verification-code', ['email' => 'worker@example.test'])->assertStatus(429);
        $this->travel(61)->seconds();
        $this->sendCode();
        $this->postJson('/api/v1/hrm/employees', $this->payload(['email_verification_token' => $token]))->assertUnprocessable();
        Mail::assertSent(EmployeeVerificationCode::class, 2);
    }

    public function test_expired_codes_and_verified_tokens_cannot_create_an_employee(): void
    {
        Mail::fake();
        $this->hr();
        $code = $this->sendCode();
        $token = $this->verify($code);
        $this->travel(16)->minutes();
        $this->postJson('/api/v1/auth/verify-code', ['email' => 'worker@example.test', 'code' => $code])->assertUnprocessable();
        $this->postJson('/api/v1/hrm/employees', $this->payload(['email_verification_token' => $token]))->assertUnprocessable();
    }

    public function test_failed_delivery_rolls_back_the_challenge(): void
    {
        $this->hr();
        Mail::shouldReceive('to')->once()->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('SMTP unavailable'));
        $this->postJson('/api/v1/auth/send-verification-code', ['email' => 'worker@example.test'])->assertStatus(503);
        $this->assertDatabaseCount('employee_email_verifications', 0);
    }

    public function test_missing_delivery_configuration_and_existing_addresses_are_rejected(): void
    {
        Mail::fake();
        $this->hr();
        $this->postJson('/api/v1/auth/send-verification-code', ['email' => 'hr@example.test'])->assertUnprocessable();
        $this->postJson('/api/v1/auth/send-verification-code', ['email' => 'not-an-email'])->assertUnprocessable();
        $this->app->detectEnvironment(fn () => 'local');
        config(['mail.default' => 'log']);
        $this->postJson('/api/v1/auth/send-verification-code', ['email' => 'worker@example.test'])->assertStatus(503);
        Mail::assertNothingSent();
    }
}
