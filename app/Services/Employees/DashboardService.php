<?php

declare(strict_types=1);

namespace App\Services\Employees;

use App\Services\BaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardService extends BaseService
{
    public function __construct(private EmployeeAccess $access) {}

    public function metrics(Request $request): array
    {
        $actor = $this->access->actor($request);
        $ids = DB::table('employees')->where('company_id', $actor->company_id)->where('status', 'Active')->pluck('employee_full_id');
        $total = $ids->count();
        $counts = function (string $date) use ($ids, $total): array {
            $records = DB::table('attendance_records')->whereIn('employee_full_id', $ids)->where('date', $date)->get()->unique('employee_full_id');
            $present = $records->where('status', 'Present')->count();
            $late = $records->where('status', 'Late')->count();
            $leave = $records->where('status', 'Leave')->count();

            return ['present' => $present, 'late' => $late, 'leave' => $leave, 'absent' => max(0, $total - $present - $late - $leave), 'rate' => sprintf('%.1f%%', $total ? (($present + $late) / $total) * 100 : 0)];
        };
        $today = now()->toDateString();
        $daily = $counts($today);
        $kpis = ['total_employees' => $total, 'present_today' => $daily['present'], 'late_today' => $daily['late'], 'on_leave_today' => $daily['leave'], 'absent_today' => $daily['absent'], 'attendance_rate' => $daily['rate']];
        $weekly = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $weekly[] = ['day' => $date->format('D'), 'date' => $date->toDateString()] + $counts($date->toDateString());
        }
        $pending = fn (string $table, string $status) => DB::table($table)->whereIn('employee_full_id', $ids)->where('status', $status)->count();

        return $kpis + ['kpis' => $kpis, 'weekly_attendance' => $weekly,
            'pending_tasks' => ['leave_recommendations' => $pending('leave_applications', 'Pending Recommend'), 'leave_approvals' => $pending('leave_applications', 'Pending Approve'), 'late_requests' => 0, 'short_leaves' => 0, 'outworks' => 0, 'loan_applications' => 0],
            'today_status' => DB::table('attendance_records')->where('employee_full_id', $actor->employee_full_id)->where('date', $today)->first()];
    }
}
