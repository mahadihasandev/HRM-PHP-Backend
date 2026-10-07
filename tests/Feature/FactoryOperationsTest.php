<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FactoryOperationsTest extends TestCase
{
    use RefreshDatabase;

    private function employee(int $company = 7, string $department = 'Administration'): User
    {
        $email = "manager$company@example.test";
        DB::table('employees')->insert(['employee_id' => $company, 'employee_full_id' => "RMG-$company", 'name' => 'Factory Manager', 'email' => $email, 'company_id' => $company, 'company' => 'Garments Company', 'department' => $department, 'designation' => 'Manager', 'status' => 'Active']);

        return User::factory()->create(['email' => $email]);
    }

    public function test_factory_setup_production_validation_and_company_isolation(): void
    {
        $this->getJson('/api/v1/hrm/factory/operations')->assertUnauthorized();
        $this->actingAs($this->employee(), 'sanctum');
        $factory = $this->postJson('/api/v1/hrm/factory/records', ['type' => 'setup', 'kind' => 'factory', 'code' => 'F1', 'name' => 'Gazipur Unit', 'active' => true, 'details' => ['address' => 'Gazipur']])->assertOk()->json('data.id');
        $line = ['type' => 'setup', 'kind' => 'line', 'code' => 'L1', 'name' => 'Sewing Line 1', 'active' => true, 'details' => ['factory_code' => 'F1', 'section' => 'Sewing', 'capacity' => 40]];
        $this->postJson('/api/v1/hrm/factory/records', $line)->assertOk();
        $this->postJson('/api/v1/hrm/factory/records', $line)->assertUnprocessable();
        $production = ['type' => 'production', 'date' => '2026-10-07', 'factory_code' => 'F1', 'line_code' => 'L1', 'order_ref' => 'ORD001', 'style' => 'T-Shirt', 'target' => 1000, 'completed' => 900, 'rejected' => 10];
        $id = $this->postJson('/api/v1/hrm/factory/records', $production)->assertOk()->json('data.id');
        $this->postJson('/api/v1/hrm/factory/records', $production)->assertUnprocessable();
        $this->postJson('/api/v1/hrm/factory/records', array_merge($production, ['id' => $id, 'rejected' => 901]))->assertUnprocessable();
        $this->postJson('/api/v1/hrm/factory/records', array_merge($production, ['id' => $id, 'completed' => 950]))->assertOk()->assertJsonPath('data.completed', 950);
        $this->postJson('/api/v1/hrm/factory/records', ['type' => 'setup', 'kind' => 'shift', 'code' => 'NIGHT', 'name' => 'Night shift', 'active' => true, 'details' => ['start' => '22:00', 'end' => '06:00', 'break_minutes' => 60]])->assertOk();
        $this->postJson('/api/v1/hrm/factory/records', ['type' => 'setup', 'kind' => 'shift', 'code' => 'SHORT', 'name' => 'Invalid shift', 'active' => true, 'details' => ['start' => '08:00', 'end' => '09:00', 'break_minutes' => 60]])->assertUnprocessable();
        $this->postJson('/api/v1/hrm/factory/records', ['type' => 'safety', 'date' => '2026-10-07', 'factory_code' => 'F1', 'category' => 'training', 'title' => 'Fire drill', 'owner' => 'Floor supervisor', 'status' => 'open', 'notes' => 'Train all shifts'])->assertOk();
        $this->getJson('/api/v1/hrm/factory/operations')->assertJsonCount(3, 'data.setups')->assertJsonCount(1, 'data.production')->assertJsonCount(1, 'data.safety');
        $this->actingAs($this->employee(8), 'sanctum');
        $this->getJson('/api/v1/hrm/factory/operations')->assertJsonCount(0, 'data.setups');
        $this->postJson('/api/v1/hrm/factory/records', ['type' => 'setup', 'id' => $factory, 'kind' => 'factory', 'code' => 'F1', 'name' => 'Other Company', 'active' => true, 'details' => ['address' => 'Dhaka']])->assertNotFound();
    }

    public function test_spoofed_admin_headers_cannot_grant_payroll_access(): void
    {
        $this->postJson('/api/v1/hrm/permissions/employee/RMG-7', ['permissions' => ['module.salary' => true]], ['X-Operator-Id' => 'SMT-0001', 'X-Admin-Role' => 'admin'])->assertUnauthorized();
        $this->actingAs($this->employee(7, 'Engineering'), 'sanctum');
        $this->postJson('/api/v1/hrm/permissions/employee/RMG-7', ['permissions' => ['module.salary' => true]], ['X-Operator-Id' => 'SMT-0001', 'X-Admin-Role' => 'admin'])->assertForbidden();
        $this->postJson('/api/v1/hrm/employee-create', ['name' => 'Fake Admin', 'designation' => 'Admin', 'department' => 'Administration'])->assertForbidden();
    }
}
