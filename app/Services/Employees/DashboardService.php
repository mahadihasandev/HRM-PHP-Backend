<?php

declare(strict_types=1);

namespace App\Services\Employees;

use App\Services\BaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService extends BaseService
{
    public function __construct(private EmployeeAccess $access) {}

    public function metrics(Request $request): array
    {
        $actor = $this->access->actor($request);
        $cacheKey = "dashboard_metrics_{$actor->company_id}_{$actor->employee_full_id}";

        return Cache::remember($cacheKey, 15, function () use ($actor) {
            $ids = DB::table('employees')->where('company_id', $actor->company_id)->where('status', 'Active')->pluck('employee_full_id');
            $total = $ids->count();
            $today = now()->toDateString();
            $startDate = now()->subDays(6)->toDateString();

            // Fetch all attendance records for the 7-day range in a SINGLE batch query
            $recordsByDate = DB::table('attendance_records')
                ->whereIn('employee_full_id', $ids)
                ->whereBetween('date', [$startDate, $today])
                ->get()
                ->groupBy('date');

            $counts = function (string $date) use ($recordsByDate, $total): array {
                $records = ($recordsByDate->get($date) ?? collect())->unique('employee_full_id');
                $present = $records->where('status', 'Present')->count();
                $late = $records->where('status', 'Late')->count();
                $leave = $records->where('status', 'Leave')->count();

                return [
                    'present' => $present,
                    'late' => $late,
                    'leave' => $leave,
                    'absent' => max(0, $total - $present - $late - $leave),
                    'rate' => sprintf('%.1f%%', $total ? (($present + $late) / $total) * 100 : 0),
                ];
            };

            $daily = $counts($today);
            $kpis = [
                'total_employees' => $total,
                'present_today' => $daily['present'],
                'late_today' => $daily['late'],
                'on_leave_today' => $daily['leave'],
                'absent_today' => $daily['absent'],
                'attendance_rate' => $daily['rate'],
            ];

            $weekly = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $weekly[] = ['day' => $date->format('D'), 'date' => $date->toDateString()] + $counts($date->toDateString());
            }

            // Single query for pending leave counts
            $leaveCounts = DB::table('leave_applications')
                ->whereIn('employee_full_id', $ids)
                ->whereIn('status', ['Pending Recommend', 'Pending Approve'])
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status');

            $todayStatus = DB::table('attendance_records')
                ->where('employee_full_id', $actor->employee_full_id)
                ->where('date', $today)
                ->first();

            return $kpis + [
                'kpis' => $kpis,
                'weekly_attendance' => $weekly,
                'pending_tasks' => [
                    'leave_recommendations' => (int) ($leaveCounts['Pending Recommend'] ?? 0),
                    'leave_approvals' => (int) ($leaveCounts['Pending Approve'] ?? 0),
                    'late_requests' => 0,
                    'short_leaves' => 0,
                    'outworks' => 0,
                    'loan_applications' => 0,
                ],
                'today_status' => $todayStatus,
            ];
        });
    }
}
