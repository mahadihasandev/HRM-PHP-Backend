<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PeopleOperationsTest extends TestCase
{
    use RefreshDatabase;

    private function employee(string $fullId, int $company = 7, string $department = 'Administration'): User
    {
        $email = strtolower($fullId).'@example.test';
        DB::table('employees')->insert(['employee_id' => random_int(1, 1000000), 'employee_full_id' => $fullId, 'name' => $fullId,
            'email' => $email, 'company_id' => $company, 'company' => 'Example Garments', 'designation' => 'Operator', 'department' => $department, 'status' => 'Active']);

        return User::factory()->create(['email' => $email]);
    }

    private function document(string $employee = 'WORKER'): array
    {
        return ['kind' => 'document', 'employee_full_id' => $employee, 'title' => 'Appointment letter',
            'start_date' => '2026-10-01', 'due_date' => '2027-10-01', 'status' => 'pending',
            'details' => ['category' => 'appointment', 'reference' => 'HR001']];
    }

    private function createFactorySetup(): void
    {
        foreach ([['kind' => 'factory', 'code' => 'F1', 'name' => 'Gazipur', 'details' => ['address' => 'Gazipur']],
            ['kind' => 'shift', 'code' => 'D1', 'name' => 'Day shift', 'details' => ['start' => '08:00', 'end' => '17:00', 'break_minutes' => 60]],
            ['kind' => 'line', 'code' => 'L1', 'name' => 'Sewing 1', 'details' => ['factory_code' => 'F1', 'capacity' => 30, 'section' => 'Sewing']]] as $setup) {
            $this->postJson('/api/v1/hrm/factory/records', $setup + ['type' => 'setup', 'active' => true])->assertOk();
        }
    }

    public function test_documents_require_permission_and_private_files_are_scoped(): void
    {
        Storage::fake('local');
        $this->getJson('/api/v1/hrm/people/overview')->assertUnauthorized();
        $manager = $this->employee('MANAGER');
        $worker = $this->employee('WORKER', 7, 'Sewing');
        $other = $this->employee('OTHER', 8);
        $this->actingAs($manager, 'sanctum');
        $id = $this->postJson('/api/v1/hrm/people/records', $this->document())->assertOk()->assertJsonPath('data.version', 1)->json('data.id');
        $this->postJson('/api/v1/hrm/people/records', $this->document('OTHER'))->assertUnprocessable();
        $this->postJson('/api/v1/hrm/people/records', array_replace($this->document(), ['employee_full_id' => null]))->assertUnprocessable();
        $this->postJson("/api/v1/hrm/people/records/$id/files", ['file' => UploadedFile::fake()->create('letter.pdf', 20, 'application/pdf')])->assertOk()->assertJsonPath('data.version', 2)->assertJsonMissingPath('data.files.0.path');
        $fileId = DB::table('hr_record_files')->value('id');
        $this->get("/api/v1/hrm/people/records/$id/files/$fileId")->assertOk()->assertDownload('letter.pdf');
        $this->postJson("/api/v1/hrm/people/records/$id/files", ['file' => UploadedFile::fake()->create('bad.html', 20, 'text/html')])->assertUnprocessable();
        $this->actingAs($worker, 'sanctum');
        $this->getJson('/api/v1/hrm/people/overview')->assertOk()->assertJsonPath('data.capabilities.manage', false)->assertJsonCount(0, 'data.employees');
        $this->getJson('/api/v1/hrm/people/records?kind=document')->assertJsonCount(1, 'data.records');
        $this->get("/api/v1/hrm/people/records/$id/files/$fileId")->assertOk();
        $this->postJson('/api/v1/hrm/people/records', $this->document())->assertForbidden();
        $this->postJson("/api/v1/hrm/people/records/$id/files", ['file' => UploadedFile::fake()->create('worker.pdf', 20)])->assertForbidden();
        $this->actingAs($manager, 'sanctum');
        DB::table('employees')->where('employee_full_id', 'WORKER')->update(['status' => 'Inactive']);
        $this->postJson('/api/v1/hrm/people/records', array_replace($this->document(), ['id' => $id, 'version' => 2, 'status' => 'archived']))->assertOk();
        $this->actingAs($other, 'sanctum');
        $this->getJson('/api/v1/hrm/people/records?kind=document')->assertJsonCount(0, 'data.records');
        $this->getJson("/api/v1/hrm/people/records/$id")->assertNotFound();
        $this->get("/api/v1/hrm/people/records/$id/files/$fileId", ['Accept' => 'application/json'])->assertNotFound();
    }

    public function test_grievances_use_real_worker_and_separate_handler_permission(): void
    {
        $manager = $this->employee('MANAGER');
        $worker = $this->employee('WORKER', 7, 'Engineering');
        $peer = $this->employee('PEER', 7, 'Engineering');
        $this->actingAs($worker, 'sanctum');
        $body = ['kind' => 'grievance', 'employee_full_id' => 'PEER', 'title' => 'Wage query', 'start_date' => now('Asia/Dhaka')->toDateString(),
            'due_date' => null, 'status' => 'open', 'details' => ['category' => 'wages', 'priority' => 'high', 'description' => 'Please review my statement', 'resolution' => 'spoofed response']];
        $id = $this->postJson('/api/v1/hrm/people/records', $body)->assertOk()->assertJsonPath('data.employee_full_id', 'WORKER')->assertJsonPath('data.details.resolution', null)->json('data.id');
        $this->postJson('/api/v1/hrm/people/records', $body + ['id' => $id, 'version' => 1])->assertForbidden();
        $this->actingAs($peer, 'sanctum');
        $this->getJson('/api/v1/hrm/people/records?kind=grievance')->assertJsonCount(0, 'data.records');
        $this->getJson("/api/v1/hrm/people/records/$id")->assertNotFound();
        $this->actingAs($manager, 'sanctum');
        // HR management alone cannot override a revoked grievance-handling permission.
        DB::table('employee_permissions')->insert(['employee_full_id' => 'MANAGER', 'permission_key' => 'action.people.grievances', 'is_granted' => false]);
        $this->getJson('/api/v1/hrm/people/records?kind=grievance')->assertJsonCount(0, 'data.records');
        $this->postJson('/api/v1/hrm/people/records', $body + ['id' => $id, 'version' => 1])->assertForbidden();
        DB::table('employee_permissions')->where('employee_full_id', 'MANAGER')->delete();
        $resolved = array_replace_recursive($body, ['id' => $id, 'version' => 1, 'status' => 'resolved', 'details' => ['resolution' => 'Statement reviewed with worker']]);
        $this->postJson('/api/v1/hrm/people/records', $resolved)->assertOk()->assertJsonPath('data.version', 2)->assertJsonPath('data.employee_full_id', 'WORKER')->assertJsonCount(2, 'data.events');
        $this->postJson('/api/v1/hrm/people/records', $resolved)->assertStatus(409);
        $this->postJson('/api/v1/hrm/people/records', array_replace($resolved, ['kind' => 'incident', 'version' => 2]))->assertUnprocessable();
    }

    public function test_shift_overlap_training_attendance_and_calendar(): void
    {
        $manager = $this->employee('MANAGER');
        $worker = $this->employee('WORKER', 7, 'Engineering');
        $this->employee('PEER', 7, 'Engineering');
        $this->employee('OTHER', 8);
        $this->actingAs($manager, 'sanctum');
        $this->createFactorySetup();
        $body = ['kind' => 'roster', 'title' => 'Sewing duty', 'employee_full_id' => 'WORKER', 'start_date' => '2026-10-07', 'due_date' => '2026-10-10', 'status' => 'active',
            'details' => ['factory_code' => 'F1', 'shift_code' => 'D1', 'line_code' => 'L1']];
        $id = $this->postJson('/api/v1/hrm/people/records', $body)->assertOk()->json('data.id');
        $this->postJson('/api/v1/hrm/people/records', array_replace($body, ['start_date' => '2026-10-10', 'due_date' => '2026-10-12']))->assertUnprocessable();
        $this->postJson('/api/v1/hrm/people/records', array_replace($body, ['due_date' => null]))->assertUnprocessable();
        $this->postJson('/api/v1/hrm/people/records', array_replace($body, ['details' => ['factory_code' => 'F1', 'shift_code' => 'FOREIGN']]))->assertUnprocessable();
        $this->postJson('/api/v1/hrm/people/records', array_replace($body, ['id' => $id, 'version' => 1, 'status' => 'cancelled']))->assertOk();
        $this->postJson('/api/v1/hrm/people/records', $body)->assertOk();
        $training = ['kind' => 'training', 'title' => 'Fire drill', 'start_date' => '2026-10-07', 'due_date' => '2026-10-08', 'status' => 'completed',
            'details' => ['category' => 'fire_safety', 'trainer' => 'Trainer', 'location' => 'Factory F1', 'participants' => [['employee_full_id' => 'WORKER', 'result' => 'attended'], ['employee_full_id' => 'PEER', 'result' => 'absent']]]];
        $trainingId = $this->postJson('/api/v1/hrm/people/records', $training)->assertOk()->json('data.id');
        $this->postJson('/api/v1/hrm/people/records', array_replace_recursive($training, ['details' => ['participants' => [['employee_full_id' => 'OTHER']]]]))->assertUnprocessable();
        $this->postJson('/api/v1/hrm/people/records', array_replace($training, ['status' => 'planned']))->assertUnprocessable();
        $this->postJson('/api/v1/hrm/people/records', ['kind' => 'holiday', 'title' => 'Factory closure', 'start_date' => '2026-10-15', 'due_date' => '2026-10-16', 'status' => 'scheduled', 'details' => ['category' => 'company', 'paid' => true]])->assertOk();
        $this->actingAs($worker, 'sanctum');
        $this->travelTo(Carbon::parse('2026-10-07 12:00:00', 'Asia/Dhaka'));
        $this->getJson('/api/v1/hrm/employee-current-shift')->assertOk()->assertJsonPath('data.shift_name', 'Day shift')->assertJsonPath('data.standard_duty_hours', 8);
        $this->getJson('/api/v1/hrm/people/records?kind=holiday')->assertJsonCount(1, 'data.records');
        $this->getJson('/api/v1/hrm/people/records?kind=roster')->assertJsonCount(2, 'data.records');
        $this->getJson('/api/v1/hrm/people/records?kind=training')->assertJsonCount(1, 'data.records')->assertJsonCount(1, 'data.records.0.details.participants')->assertJsonPath('data.records.0.details.participants.0.result', 'attended');
        $this->getJson("/api/v1/hrm/people/records/$trainingId")->assertJsonCount(1, 'data.details.participants');
    }

    public function test_pagination_filtering_peer_privacy_and_workspace_revocation(): void
    {
        $manager = $this->employee('MANAGER');
        $worker = $this->employee('WORKER', 7, 'Engineering');
        $peer = $this->employee('PEER', 7, 'Engineering');
        $this->actingAs($manager, 'sanctum');
        for ($index = 0; $index < 26; $index++) {
            $this->postJson('/api/v1/hrm/people/records', array_replace($this->document(), ['title' => 'Contract '.$index]))->assertOk();
        }
        $this->getJson('/api/v1/hrm/people/records?kind=document')->assertJsonCount(25, 'data.records')->assertJsonPath('data.pagination.total', 26);
        $this->getJson('/api/v1/hrm/people/records?kind=document&page=2')->assertJsonCount(1, 'data.records');
        $this->getJson('/api/v1/hrm/people/records?kind=document&q=Contract%2025')->assertJsonCount(1, 'data.records');
        $this->getJson('/api/v1/hrm/people/records?kind=document&status=verified')->assertJsonCount(0, 'data.records');
        $this->actingAs($peer, 'sanctum');
        $this->getJson('/api/v1/hrm/people/records?kind=document')->assertJsonCount(0, 'data.records');
        $this->getJson('/api/v1/hrm/people/records/1')->assertNotFound();
        $this->actingAs($worker, 'sanctum');
        $this->getJson('/api/v1/hrm/people/records/1')->assertOk();
        DB::table('employee_permissions')->insert(['employee_full_id' => 'WORKER', 'permission_key' => 'module.people', 'is_granted' => false]);
        $this->getJson('/api/v1/hrm/people/overview')->assertForbidden();
        $this->getJson('/api/v1/hrm/people/records/1')->assertForbidden();
    }

    public function test_incident_closure_and_expiry_overview_use_saved_records(): void
    {
        $manager = $this->employee('MANAGER');
        $this->employee('WORKER', 7, 'Engineering');
        $this->actingAs($manager, 'sanctum');
        $this->createFactorySetup();
        $today = now('Asia/Dhaka')->toDateString();
        $this->postJson('/api/v1/hrm/people/records', array_replace($this->document(), ['start_date' => $today, 'due_date' => now('Asia/Dhaka')->addDays(10)->toDateString()]))->assertOk();
        $incident = ['kind' => 'incident', 'title' => 'Blocked exit', 'start_date' => now('Asia/Dhaka')->subDays(5)->toDateString(), 'due_date' => now('Asia/Dhaka')->subDay()->toDateString(), 'status' => 'open',
            'details' => ['category' => 'near_miss', 'severity' => 'high', 'factory_code' => 'F1', 'description' => 'Exit blocked by cartons']];
        $id = $this->postJson('/api/v1/hrm/people/records', $incident)->assertOk()->json('data.id');
        $this->getJson('/api/v1/hrm/people/overview')->assertJsonPath('data.stats.documents_due', 1)->assertJsonPath('data.stats.overdue_actions', 1)->assertJsonPath('data.stats.open_concerns', 1);
        $this->postJson('/api/v1/hrm/people/records', array_replace($incident, ['id' => $id, 'version' => 1, 'status' => 'completed']))->assertUnprocessable();
        $this->postJson('/api/v1/hrm/people/records', array_replace_recursive($incident, ['id' => $id, 'version' => 1, 'status' => 'completed', 'details' => ['corrective_action' => 'Exit cleared and checked', 'responsible_person' => 'Safety manager']]))->assertOk();
        $this->getJson('/api/v1/hrm/people/overview')->assertJsonPath('data.stats.overdue_actions', 0)->assertJsonPath('data.stats.open_concerns', 0);
    }
}
