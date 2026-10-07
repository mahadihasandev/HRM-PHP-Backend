<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PayrollWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function employee(string $id = 'RMG-001', int $company = 7, string $department = 'Administration'): User
    {
        $email = strtolower($id).'@example.com';
        DB::table('employees')->insert(['employee_id' => random_int(1, 1000000), 'employee_full_id' => $id, 'name' => 'Test Worker', 'email' => $email, 'company_id' => $company, 'company' => 'Example Garments Ltd.', 'department' => $department, 'designation' => 'Operator', 'status' => 'Active']);

        return User::factory()->create(['email' => $email]);
    }

    private function payload(): array
    {
        return ['month' => '2026-09', 'title' => 'September payroll', 'bank_name' => 'Test Bank', 'bank_branch' => 'Dhaka', 'debit_account' => '000123456789', 'signatory' => 'Accounts Manager', 'signatory_title' => 'Finance', 'rows' => [['employee_full_id' => 'RMG-001', 'basic_salary' => 10000, 'house_rent' => 5000, 'overtime_hours' => 10, 'overtime_rate' => 100, 'absence_deduction' => 500, 'payment_method' => 'bank', 'bank_name' => 'Test Bank', 'bank_account_no' => '001234567890', 'routing_no' => '012345678', 'net_payable' => 15500]]];
    }

    public function test_payroll_lifecycle_uses_snapshot_and_prevents_duplicate_monthly_payment(): void
    {
        $this->actingAs($this->employee(), 'sanctum');
        $payload = $this->payload();
        $this->postJson('/api/v1/hrm/payroll/preview', $payload)->assertOk()->assertJsonPath('data.valid', true)->assertJsonPath('data.summary.net_payable', 15500);
        $run = $this->postJson('/api/v1/hrm/payroll/runs', $payload)->assertCreated()->assertJsonPath('data.status', 'draft')->json('data.id');
        DB::table('employees')->where('employee_full_id', 'RMG-001')->update(['basic_salary' => 99999]);
        $this->getJson('/api/v1/hrm/payroll/runs')->assertOk()->assertJsonPath('data.0.summary.net_payable', 15500)->assertJsonCount(0, 'data.0.items');
        $this->getJson("/api/v1/hrm/payroll/runs/$run")->assertOk()->assertJsonPath('data.items.0.basic_salary', 10000)->assertJsonPath('data.items.0.bank_account_no', '001234567890');
        $this->postJson("/api/v1/hrm/payroll/runs/$run/action", ['action' => 'paid', 'payment_date' => '2026-10-01', 'payment_reference' => 'TX001'])->assertStatus(409);
        $this->postJson("/api/v1/hrm/payroll/runs/$run/action", ['action' => 'approve'])->assertOk()->assertJsonPath('data.status', 'approved');
        $this->postJson("/api/v1/hrm/payroll/runs/$run/action", ['action' => 'approve'])->assertStatus(409);
        $this->postJson("/api/v1/hrm/payroll/runs/$run/action", ['action' => 'paid', 'payment_date' => '2026-10-01', 'payment_reference' => 'TX001'])->assertOk()->assertJsonPath('data.status', 'paid');
        $this->postJson('/api/v1/hrm/payroll/runs', $payload)->assertUnprocessable();
        $this->assertDatabaseCount('payroll_items', 1);
    }

    public function test_preview_reports_duplicate_unknown_invalid_bank_and_net_mismatch_rows_without_writing(): void
    {
        $this->actingAs($this->employee(), 'sanctum');
        $payload = $this->payload();
        $payload['rows'][] = $payload['rows'][0];
        $payload['rows'][0]['bank_account_no'] = 'invalid';
        $payload['rows'][0]['net_payable'] = 1;
        $payload['rows'][] = ['employee_full_id' => 'MISSING', 'basic_salary' => -5, 'overtime_hours' => 1, 'overtime_rate' => 0, 'payment_method' => 'cash'];
        $this->postJson('/api/v1/hrm/payroll/preview', $payload)->assertOk()->assertJsonPath('data.valid', false)->assertJsonCount(3, 'data.errors');
        $this->postJson('/api/v1/hrm/payroll/runs', $payload)->assertUnprocessable();
        $this->assertDatabaseCount('payroll_runs', 0);
    }

    public function test_auth_permissions_and_company_boundaries_and_private_attachments(): void
    {
        $this->getJson('/api/v1/hrm/payroll/runs')->assertUnauthorized();
        $admin = $this->employee();
        $this->actingAs($admin, 'sanctum');
        $run = $this->postJson('/api/v1/hrm/payroll/runs', $this->payload())->assertCreated()->json('data.id');
        Storage::fake('local');
        $this->post("/api/v1/hrm/payroll/runs/$run/attachments", ['file' => UploadedFile::fake()->createWithContent('approval.txt', 'Approved salary schedule')], ['Accept' => 'application/json'])->assertOk();
        $attachment = DB::table('payroll_attachments')->first();
        $this->get("/api/v1/hrm/payroll/runs/$run/attachments/$attachment->id")->assertOk();
        $other = $this->employee('OTHER-001', 8);
        $this->actingAs($other, 'sanctum');
        $this->getJson('/api/v1/hrm/payroll/runs')->assertJsonCount(0, 'data');
        $this->getJson("/api/v1/hrm/payroll/runs/$run/attachments/$attachment->id")->assertNotFound();
        $this->postJson("/api/v1/hrm/payroll/runs/$run/action", ['action' => 'approve'])->assertNotFound();
        $ordinary = $this->employee('EMP-002', 7, 'Engineering');
        $this->actingAs($ordinary, 'sanctum');
        $this->getJson('/api/v1/hrm/payroll/runs')->assertForbidden();
    }

    public function test_cash_payroll_requires_no_bank_and_zero_allowances_stay_zero(): void
    {
        $this->actingAs($this->employee(), 'sanctum');
        $payload = ['month' => '2026-09', 'title' => 'Cash payroll', 'rows' => [['employee_full_id' => 'RMG-001', 'basic_salary' => 10000, 'payment_method' => 'cash']]];
        $run = $this->postJson('/api/v1/hrm/payroll/runs', $payload)->assertCreated()->assertJsonPath('data.items.0.house_rent', 0)->assertJsonPath('data.summary.cash_total', 10000)->json('data.id');
        $this->postJson("/api/v1/hrm/payroll/runs/$run/action", ['action' => 'approve'])->assertOk();
    }

    public function test_login_rejects_default_password_bypass_and_unprovisioned_employee(): void
    {
        $user = $this->employee();
        $this->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'password123'])->assertUnauthorized();
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $user->delete();
        $this->postJson('/api/v1/login', ['email' => 'rmg-001@example.com', 'password' => 'chosen-by-attacker'])->assertUnauthorized();
    }

    public function test_draft_discard_releases_employee_month_and_approved_run_is_retained(): void
    {
        $this->actingAs($this->employee(), 'sanctum');
        $payload = $this->payload();
        $run = $this->postJson('/api/v1/hrm/payroll/runs', $payload)->assertCreated()->json('data.id');
        $this->deleteJson("/api/v1/hrm/payroll/runs/$run")->assertOk();
        $this->assertDatabaseCount('payroll_items', 0);
        $run = $this->postJson('/api/v1/hrm/payroll/runs', $payload)->assertCreated()->json('data.id');
        $this->postJson("/api/v1/hrm/payroll/runs/$run/action", ['action' => 'approve'])->assertOk();
        $this->deleteJson("/api/v1/hrm/payroll/runs/$run")->assertStatus(409);
        $this->assertDatabaseCount('payroll_items', 1);
    }

    public function test_bank_header_validation_and_malformed_row_list_are_rejected(): void
    {
        $this->actingAs($this->employee(), 'sanctum');
        $payload = $this->payload();
        $payload['debit_account'] = '';
        $this->postJson('/api/v1/hrm/payroll/preview', $payload)->assertOk()->assertJsonPath('data.valid', false)->assertJsonPath('data.errors.0.row', 1);
        $this->postJson('/api/v1/hrm/payroll/runs', $payload)->assertUnprocessable();
        $payload['rows'] = ['bad-key' => $payload['rows'][0]];
        $this->postJson('/api/v1/hrm/payroll/preview', $payload)->assertUnprocessable();
    }

    public function test_previous_paid_history_is_preserved_and_prevents_duplicate_salary(): void
    {
        $this->actingAs($this->employee(), 'sanctum');
        DB::table('payslips')->insert(['employee_full_id' => 'RMG-001', 'employee_name' => 'Test Worker', 'designation' => 'Operator', 'department' => 'Production', 'month' => '2026-09', 'company' => 'Example Garments Ltd.', 'basic_salary' => 10000, 'house_rent' => 0, 'medical_allowance' => 0, 'conveyance' => 0, 'total_earnings' => 10000, 'total_deductions' => 0, 'net_payable' => 10000, 'status' => 'Paid']);
        $this->getJson('/api/v1/hrm/payroll/history')->assertOk()->assertJsonPath('data.0.net_payable', 10000)->assertJsonPath('data.0.overtime_amount', 0);
        $this->postJson('/api/v1/hrm/payroll/preview', $this->payload())->assertJsonPath('data.valid', false);
        $this->postJson('/api/v1/hrm/payroll/runs', $this->payload())->assertUnprocessable();
        $this->postJson('/api/v1/hrm/overtime/set-rate', ['employee_full_id' => 'RMG-001', 'overtime_rate' => 150])->assertOk();
        $this->getJson('/api/v1/hrm/payroll/history')->assertJsonPath('data.0.net_payable', 10000);
    }
}
