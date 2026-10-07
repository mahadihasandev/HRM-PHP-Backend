<?php

declare(strict_types=1);

namespace App\Services\Employees;

use App\Services\BaseService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveService extends BaseService
{
    public function __construct(private EmployeeAccess $access) {}

    public function query(Request $request)
    {
        $actor = $this->access->actor($request);
        $query = DB::table('leave_applications')->whereIn('employee_full_id', DB::table('employees')->where('company_id', $actor->company_id)->select('employee_full_id'));
        if (! $this->access->allowed($actor, 'action.leave.approve')) {
            $query->where('employee_full_id', $actor->employee_full_id);
        }

        return $query;
    }

    public function apply(Request $request): object
    {
        $actor = $this->access->actor($request);
        abort_unless($this->access->allowed($actor, 'action.leave.apply'), 403, 'Leave application permission is required.');
        $values = $request->validated();
        $days = (int) CarbonImmutable::parse($values['from_date'])->diffInDays(CarbonImmutable::parse($values['to_date'])) + 1;
        $types = [1 => 'Casual Leave', 2 => 'Earned Leave', 3 => 'Sick Leave', 4 => 'Maternity Leave'];
        $id = DB::table('leave_applications')->insertGetId($values + ['employee_id' => $actor->employee_id, 'employee_full_id' => $actor->employee_full_id, 'employee_name' => $actor->name, 'leave_type' => $types[(int) $values['leave_type_id']], 'days_count' => $days, 'status' => 'Pending Recommend', 'applied_at' => now(), 'created_at' => now(), 'updated_at' => now()]);

        return DB::table('leave_applications')->where('id', $id)->first();
    }

    public function transition(Request $request, int $id, string $action): object
    {
        return DB::transaction(function () use ($request, $id, $action) {
            $actor = $this->access->actor($request);
            $record = $this->query($request)->where('id', $id)->lockForUpdate()->firstOrFail();
            $canApprove = $this->access->allowed($actor, 'action.leave.approve');
            abort_unless($canApprove || ($action === 'cancel' && $record->employee_full_id === $actor->employee_full_id), 403, 'Leave approval permission is required.');
            $values = $request->validated();
            if ($action === 'recommend') {
                abort_unless($record->status === 'Pending Recommend', 409, 'Only pending recommendations can be recommended.');
                $values = ['status' => 'Pending Approve', 'recommended_by' => $actor->name, 'recommend_note' => $values['note'] ?? null];
            } elseif ($action === 'approve') {
                abort_unless($record->status === 'Pending Approve', 409, 'Recommend the application before approval.');
                $values = ['status' => 'Approved', 'approved_by' => $actor->name, 'approve_note' => $values['note'] ?? null];
            } else {
                abort_unless(str_starts_with($record->status, 'Pending'), 409, 'Only pending leave can be cancelled.');
                $values = ['status' => 'Cancelled'];
            }
            DB::table('leave_applications')->where('id', $id)->update($values + ['updated_at' => now()]);

            return DB::table('leave_applications')->where('id', $id)->first();
        });
    }
}
