<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveController extends BaseApiController
{
    /**
     * Get available leave types (BLA 2006 Policy).
     */
    public function types(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'name' => 'Casual Leave (নৈমিত্তিক)', 'allocated_days' => 10, 'used_days' => 3, 'remaining_days' => 7],
                ['id' => 2, 'name' => 'Earned Leave (অর্জিত)', 'allocated_days' => 18, 'used_days' => 2, 'remaining_days' => 16],
                ['id' => 3, 'name' => 'Sick / Medical Leave (চিকিৎসা)', 'allocated_days' => 14, 'used_days' => 3, 'remaining_days' => 11],
                ['id' => 4, 'name' => 'Maternity Leave (মাতৃত্বকালীন)', 'allocated_days' => 112, 'used_days' => 0, 'remaining_days' => 112],
            ],
        ]);
    }

    /**
     * Apply for leave.
     */
    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'from_date' => 'required',
            'to_date' => 'required',
            'leave_type' => 'required',
            'reason' => 'required|string',
        ]);

        $days = (int) $request->input('days_count', 1);
        if ($days <= 0 && $request->input('from_date') && $request->input('to_date')) {
            $from = strtotime((string) $request->input('from_date'));
            $to = strtotime((string) $request->input('to_date'));
            $days = max(1, (int) round(($to - $from) / (60 * 60 * 24)) + 1);
        }

        $id = DB::table('leave_applications')->insertGetId([
            'employee_id' => 479,
            'employee_full_id' => 'SMT-0051',
            'employee_name' => 'Abdul Halim',
            'leave_type' => is_numeric($request->input('leave_type'))
                ? (intval($request->input('leave_type')) === 1 ? 'Casual Leave' : (intval($request->input('leave_type')) === 2 ? 'Earned Leave' : 'Sick Leave'))
                : $request->input('leave_type'),
            'leave_type_id' => is_numeric($request->input('leave_type')) ? intval($request->input('leave_type')) : 1,
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'days_count' => $days,
            'reason' => $request->input('reason'),
            'emergency_phone' => $request->input('emergency_phone', '01717186089'),
            'status' => 'Pending Recommend',
            'applied_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Leave application submitted successfully',
            'data' => [
                'id' => $id,
                'employee_full_id' => 'SMT-0051',
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date'),
                'days_count' => $days,
                'status' => 'Pending Recommend',
                'created_at' => now()->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Get leave application history.
     */
    public function list(Request $request): JsonResponse
    {
        $applications = DB::table('leave_applications')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $applications,
            'total' => $applications->count(),
        ]);
    }

    /**
     * Get pending leave applications for recommendation or approval.
     */
    public function pendingList(Request $request): JsonResponse
    {
        $applications = DB::table('leave_applications')
            ->where('status', 'like', 'Pending%')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $applications,
            'total' => $applications->count(),
        ]);
    }

    /**
     * Recommend leave application.
     */
    public function recommend(Request $request, $id): JsonResponse
    {
        DB::table('leave_applications')->where('id', $id)->update([
            'status' => 'Pending Approve',
            'recommended_by' => 'Abdul Halim (Line Manager)',
            'recommend_note' => $request->input('note', 'Recommended by Line Manager'),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => "Leave application #{$id} recommended successfully",
            'note' => $request->input('note', 'Recommended by Line Manager'),
        ]);
    }

    /**
     * Approve leave application.
     */
    public function approve(Request $request, $id): JsonResponse
    {
        DB::table('leave_applications')->where('id', $id)->update([
            'status' => 'Approved',
            'approved_by' => 'Director HR & Operations',
            'approve_note' => $request->input('note', 'Approved by Director'),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => "Leave application #{$id} approved successfully",
            'note' => $request->input('note', 'Approved by Director'),
        ]);
    }

    /**
     * Cancel leave application.
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        DB::table('leave_applications')->where('id', $id)->update([
            'status' => 'Cancelled',
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => "Leave application #{$id} cancelled successfully",
        ]);
    }

    /**
     * Get leave statistics and balance.
     */
    public function stats(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                'total_allocated' => 42,
                'total_used' => 8,
                'total_remaining' => 34,
                'casual_leave' => ['allocated' => 10, 'used' => 3, 'remaining' => 7],
                'annual_leave' => ['allocated' => 18, 'used' => 2, 'remaining' => 16],
                'sick_leave' => ['allocated' => 14, 'used' => 3, 'remaining' => 11],
            ],
        ]);
    }
}
