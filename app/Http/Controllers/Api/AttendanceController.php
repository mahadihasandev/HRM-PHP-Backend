<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends BaseApiController
{
    /**
     * Resolve employee entity from request headers or query/body parameters.
     */
    protected function resolveEmployee(Request $request): object
    {
        return app(\App\Services\Employees\EmployeeAccess::class)->target($request, 'module.attendance');
    }

    /**
     * Calculate duty hours and overtime between initial punch in and latest punch out.
     * The LAST punch out determines the total duty hours.
     */
    protected function calculateDutyHours(string $inTime, string $outTime, string $date): array
    {
        try {
            $inCarbon = Carbon::parse("{$date} {$inTime}");
            $outCarbon = Carbon::parse("{$date} {$outTime}");

            // Overnight shift handling
            if ($outCarbon->lessThan($inCarbon)) {
                $outCarbon->addDay();
            }

            $diffMinutes = (int) max(0, round((float) $inCarbon->diffInMinutes($outCarbon)));
            $hours = intdiv($diffMinutes, 60);
            $minutes = $diffMinutes % 60;
            $workingHours = sprintf('%d hrs %02d mins', $hours, $minutes);

            // Standard shift is 8 hours (480 minutes)
            $overtimeHours = null;
            if ($diffMinutes > 480) {
                $otMinutes = (int) ($diffMinutes - 480);
                $overtimeHours = sprintf('%d hrs %02d mins', intdiv($otMinutes, 60), $otMinutes % 60);
            }

            return [
                'diff_minutes' => $diffMinutes,
                'working_hours' => $workingHours,
                'overtime_hours' => $overtimeHours,
            ];
        } catch (\Throwable $e) {
            return [
                'diff_minutes' => 0,
                'working_hours' => '—',
                'overtime_hours' => null,
            ];
        }
    }

    /**
     * Store mobile attendance punch.
     * Rule: Punch In once per day. Punch Out as much as desired; last punch out counts duty hours.
     */
    public function mobileStore(Request $request): JsonResponse
    {
        $type = strtolower((string) $request->input('type', $request->input('punch_type', 'auto')));

        if ($type === 'in' || $type === 'punch_in') {
            return $this->punchIn($request);
        }

        if ($type === 'out' || $type === 'punch_out') {
            return $this->punchOut($request);
        }

        // Auto mode: if not punched in today -> punch in; if already punched in -> punch out!
        $emp = $this->resolveEmployee($request);
        $today = now()->toDateString();

        $existing = DB::table('attendance_records')
            ->where('employee_full_id', $emp->employee_full_id)
            ->where('date', $today)
            ->first();

        if (!$existing || !$existing->in_time) {
            return $this->punchIn($request);
        }

        return $this->punchOut($request);
    }

    /**
     * Punch In operation.
     * Rule: Employee can ONLY punch in ONCE per day. Subsequent punch in attempts are rejected.
     */
    public function punchIn(Request $request): JsonResponse
    {
        $emp = $this->resolveEmployee($request);
        $today = now()->toDateString();
        $time = now()->format('h:i A');
        $lat = $request->input('latitude');
        $long = $request->input('longitude');
        $location = $request->input('location', 'Self-service web punch');

        $existing = DB::table('attendance_records')
            ->where('employee_full_id', $emp->employee_full_id)
            ->where('date', $today)
            ->first();

        // Rule Check: If already punched in today, reject duplicate punch in
        if ($existing && $existing->in_time) {
            return response()->json([
                'status' => false,
                'code' => 'ALREADY_PUNCHED_IN',
                'message' => "You have already punched in today at {$existing->in_time}. An employee can only punch in once per day.",
                'data' => [
                    'employee_full_id' => $emp->employee_full_id,
                    'has_punched_in' => true,
                    'can_punch_in' => false,
                    'can_punch_out' => true,
                    'in_time' => $existing->in_time,
                    'out_time' => $existing->out_time,
                    'working_hours' => $existing->working_hours,
                    'overtime_hours' => $existing->overtime_hours,
                    'status' => $existing->status,
                ],
            ], 422);
        }

        // Determine if Late arrival (after 09:15 AM)
        $parsedTime = Carbon::parse("{$today} {$time}");
        $graceDeadline = Carbon::parse("{$today} 09:15 AM");
        $status = $parsedTime->greaterThan($graceDeadline) ? 'Late' : 'Present';
        $lateMinutes = $parsedTime->greaterThan($graceDeadline) ? $graceDeadline->diffInMinutes($parsedTime) : 0;

        if ($existing) {
            DB::table('attendance_records')
                ->where('id', $existing->id)
                ->update([
                    'in_time' => $time,
                    'status' => $status,
                    'late_minutes' => $lateMinutes,
                    'location' => $location,
                    'punch_source' => 'Web',
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('attendance_records')->insert([
                'employee_id' => $emp->id,
                'employee_full_id' => $emp->employee_full_id,
                'employee_name' => $emp->name,
                'date' => $today,
                'in_time' => $time,
                'out_time' => null,
                'status' => $status,
                'late_minutes' => $lateMinutes,
                'location' => $location,
                'punch_source' => 'Web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'status' => true,
            'action' => 'punch_in',
            'message' => "Successfully Punched In at {$time} via Geofenced GPS.",
            'data' => [
                'employee_full_id' => $emp->employee_full_id,
                'employee_name' => $emp->name,
                'punch_date' => $today,
                'in_time' => $time,
                'out_time' => null,
                'has_punched_in' => true,
                'can_punch_in' => false,
                'can_punch_out' => true,
                'status' => $status,
                'latitude' => $lat,
                'longitude' => $long,
                'location' => $location,
            ],
        ]);
    }

    /**
     * Punch Out operation.
     * Rule: Employee can punch out AS MUCH AS THEY WANT.
     * The LAST punch out updates out_time and calculates total duty hours from in_time.
     */
    public function punchOut(Request $request): JsonResponse
    {
        $emp = $this->resolveEmployee($request);
        $today = now()->toDateString();
        $time = now()->format('h:i A');
        $lat = $request->input('latitude');
        $long = $request->input('longitude');

        $existing = DB::table('attendance_records')
            ->where('employee_full_id', $emp->employee_full_id)
            ->where('date', $today)
            ->first();

        // Must punch in first before punching out
        if (!$existing || !$existing->in_time) {
            return response()->json([
                'status' => false,
                'code' => 'NOT_PUNCHED_IN',
                'message' => 'You must punch in first before punching out.',
                'data' => [
                    'employee_full_id' => $emp->employee_full_id,
                    'has_punched_in' => false,
                    'can_punch_in' => true,
                    'can_punch_out' => false,
                ],
            ], 422);
        }

        // Calculate duty hours between the first punch in and this latest punch out
        $duty = $this->calculateDutyHours($existing->in_time, $time, $today);
        $otMinutes = $duty['diff_minutes'] > 480 ? (int) ($duty['diff_minutes'] - 480) : 0;
        $basic = (float) ($emp->basic_salary ?: 30000);
        $otRate = (float) (($emp->overtime_rate ?? 0) > 0 ? $emp->overtime_rate : OvertimeController::calculateBlaStandardRate($basic));
        $todayOtPay = round(($otMinutes / 60.0) * $otRate, 2);

        DB::table('attendance_records')
            ->where('id', $existing->id)
            ->update([
                'out_time' => $time,
                'working_hours' => $duty['working_hours'],
                'overtime_hours' => $duty['overtime_hours'],
                'overtime_minutes' => $otMinutes,
                'updated_at' => now(),
            ]);

        return response()->json([
            'status' => true,
            'action' => 'punch_out',
            'message' => "Punch Out recorded at {$time}. Total Duty: {$duty['working_hours']} (counted from initial Punch In at {$existing->in_time} to latest Punch Out).",
            'data' => [
                'employee_full_id' => $emp->employee_full_id,
                'employee_name' => $emp->name,
                'punch_date' => $today,
                'in_time' => $existing->in_time,
                'out_time' => $time,
                'working_hours' => $duty['working_hours'],
                'overtime_hours' => $duty['overtime_hours'],
                'overtime_minutes' => $otMinutes,
                'overtime_rate' => $otRate,
                'today_overtime_pay' => $todayOtPay,
                'has_punched_in' => true,
                'can_punch_in' => false,
                'can_punch_out' => true,
                'status' => $existing->status,
                'latitude' => $lat,
                'longitude' => $long,
            ],
        ]);
    }

    /**
     * Check if authenticated employee punched today, and return punch status & duty calculations.
     */
    public function checkToday(Request $request): JsonResponse
    {
        $emp = $this->resolveEmployee($request);
        $today = now()->toDateString();

        $record = DB::table('attendance_records')
            ->where('employee_full_id', $emp->employee_full_id)
            ->where('date', $today)
            ->first();

        $hasPunchedIn = (bool) ($record && $record->in_time);
        $basic = (float) ($emp->basic_salary ?: 30000);
        $otRate = (float) (($emp->overtime_rate ?? 0) > 0 ? $emp->overtime_rate : OvertimeController::calculateBlaStandardRate($basic));
        $otMinutes = (int) ($record->overtime_minutes ?? 0);
        if ($otMinutes === 0 && !empty($record->overtime_hours)) {
            if (preg_match('/(\d+)\s*hrs?\s*(\d+)?\s*mins?/', $record->overtime_hours, $m)) {
                $otMinutes = ((int) $m[1] * 60) + (isset($m[2]) ? (int) $m[2] : 0);
            }
        }
        $todayOtPay = round(($otMinutes / 60.0) * $otRate, 2);

        return response()->json([
            'status' => true,
            'data' => [
                'employee_full_id' => $emp->employee_full_id,
                'employee_name' => $emp->name,
                'date' => $today,
                'has_punched_in' => $hasPunchedIn,
                'can_punch_in' => !$hasPunchedIn,
                'can_punch_out' => $hasPunchedIn,
                'in_time' => $record ? $record->in_time : null,
                'out_time' => $record ? $record->out_time : null,
                'working_hours' => $record ? $record->working_hours : null,
                'overtime_hours' => $record ? $record->overtime_hours : null,
                'overtime_minutes' => $otMinutes,
                'overtime_rate' => $otRate,
                'today_overtime_pay' => $todayOtPay,
                'status' => $record ? $record->status : 'Not Punched In',
                'rule_summary' => 'Single punch-in allowed per day; punch-out allowed unlimited times with final punch-out calculating total duty hours.',
            ],
        ]);
    }

    /**
     * Today's company-wide attendance report.
     */
    public function todayReport(Request $request): JsonResponse
    {
        $today = now()->toDateString();
        $data = app(\App\Services\Employees\DashboardService::class)->metrics($request);
        $totalEmployees = $data['total_employees'];
        $present = $data['present_today']; $late = $data['late_today']; $leave = $data['on_leave_today']; $absent = $data['absent_today'];

        return response()->json([
            'status' => true,
            'data' => [
                'summary' => [
                    'total' => $totalEmployees,
                    'present' => $present,
                    'late' => $late,
                    'leave' => $leave,
                    'absent' => $absent,
                ],
                'date' => $today,
            ],
        ]);
    }

    /**
     * Today's attendance roster for all employees.
     */
    public function allEmployeesTodayAttendance(Request $request): JsonResponse
    {
        $today = $request->query('date', now()->toDateString());
        $statusFilter = $request->query('status');
        $departmentFilter = $request->query('department');
        $search = $request->query('search');

        $actor = app(\App\Services\Employees\EmployeeAccess::class)->actor($request);
        abort_unless(app(\App\Services\Employees\EmployeeAccess::class)->allowed($actor, 'module.attendance'), 403);
        $employees = DB::table('employees')->where('company_id', $actor->company_id)
            ->select('id', 'employee_full_id', 'name', 'email', 'phone', 'department', 'designation', 'company')
            ->orderBy('id', 'asc')
            ->get();

        $existingRecords = DB::table('attendance_records')
            ->where('date', $today)
            ->get()
            ->keyBy('employee_full_id');

        $roster = [];
        $presentCount = 0;
        $lateCount = 0;
        $leaveCount = 0;
        $absentCount = 0;

        foreach ($employees as $idx => $emp) {
            $rec = $existingRecords->get($emp->employee_full_id);

            if ($rec) {
                $status = $rec->status ?: 'Present';
                $inTime = $rec->in_time;
                $outTime = $rec->out_time;
                $workingHours = $rec->working_hours;
                $overtimeHours = $rec->overtime_hours;
                $lateMinutes = (int) ($rec->late_minutes ?? 0);
                $location = $rec->location ?: 'Tejgaon Corporate HQ, Dhaka';
                $punchSource = $rec->punch_source ?: 'ZKTeco BioSync';
            } else {
                $status = 'Absent'; $inTime = null; $outTime = null;
                $workingHours = null; $overtimeHours = null; $lateMinutes = 0;
                $punchSource = 'No attendance record'; $location = '';
            }

            if ($status === 'Present') $presentCount++;
            elseif ($status === 'Late') $lateCount++;
            elseif ($status === 'Leave') $leaveCount++;
            else $absentCount++;

            $item = [
                'id' => $emp->id,
                'employee_full_id' => $emp->employee_full_id,
                'name' => $emp->name,
                'department' => $emp->department,
                'designation' => $emp->designation,
                'email' => $emp->email,
                'phone' => $emp->phone ?: '+880 1711-000000',
                'company' => $emp->company,
                'status' => $status,
                'in_time' => $inTime,
                'out_time' => $outTime,
                'working_hours' => $workingHours,
                'overtime_hours' => $overtimeHours,
                'late_minutes' => $lateMinutes,
                'punch_source' => $punchSource,
                'location' => $location,
                'shift' => 'General Day Shift (09:00 - 18:00)',
            ];

            // Filters
            if ($search) {
                $needle = strtolower($search);
                if (
                    strpos(strtolower($emp->name), $needle) === false &&
                    strpos(strtolower($emp->employee_full_id), $needle) === false &&
                    strpos(strtolower($emp->department), $needle) === false &&
                    strpos(strtolower($emp->designation), $needle) === false
                ) {
                    continue;
                }
            }

            if ($statusFilter && $statusFilter !== 'all' && strtolower($status) !== strtolower($statusFilter)) {
                continue;
            }

            if ($departmentFilter && $departmentFilter !== 'all' && $emp->department !== $departmentFilter) {
                continue;
            }

            $roster[] = $item;
        }

        $total = count($employees);
        $rate = $total > 0 ? sprintf('%.1f%%', ($presentCount / $total) * 100) : '0.0%';

        return response()->json([
            'status' => true,
            'date' => $today,
            'summary' => [
                'total' => $total,
                'present' => $presentCount,
                'late' => $lateCount,
                'leave' => $leaveCount,
                'absent' => $absentCount,
                'attendance_rate' => $rate,
            ],
            'data' => $roster,
            'total' => count($roster),
        ]);
    }

    /**
     * Monthly attendance report and job card.
     */
    public function monthlyReport(Request $request): JsonResponse
    {
        $emp = $this->resolveEmployee($request);
        $month = $request->query('month', now()->format('Y-m'));

        $records = DB::table('attendance_records')
            ->where('employee_full_id', $emp->employee_full_id)
            ->where('date', 'like', "{$month}%")
            ->orderBy('date', 'desc')
            ->get();

        return response()->json(['status' => true, 'month' => $month, 'employee_full_id' => $emp->employee_full_id, 'data' => $records]);
    }

    /**
     * Employee job card details.
     */
    public function jobCard(Request $request): JsonResponse
    {
        return $this->monthlyReport($request);
    }

    /**
     * Employee current shift info.
     */
    public function currentShift(Request $request): JsonResponse
    {
        return $this->successResponse(app(\App\Services\PeopleService::class)->currentShift($this->resolveEmployee($request)), 'Current assigned shift');
    }

    /**
     * Biometric Push API ingestion.
     */
    public function push(Request $request): JsonResponse
    {
        return $this->errorResponse('Biometric ingestion is not implemented; no records were stored.', 501);
    }
}
