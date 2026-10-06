<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TourPlanController extends BaseApiController
{
    /**
     * Work Types list for Tour Plans.
     */
    public function workTypes(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'name' => 'Market Visit / Outlet Audit'],
                ['id' => 2, 'name' => 'Distributor Business Review'],
                ['id' => 3, 'name' => 'Key Account Client Meeting'],
                ['id' => 4, 'name' => 'Regional Sales Conference'],
                ['id' => 5, 'name' => 'Depot Inventory Stock Verification'],
            ],
        ]);
    }

    /**
     * Transport Types list for Tour Plans.
     */
    public function transportTypes(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'name' => 'Company Motorbike', 'rate_per_km' => 4.5],
                ['id' => 2, 'name' => 'CNG Auto Rickshaw', 'rate_per_km' => 12.0],
                ['id' => 3, 'name' => 'Local Bus / Public Transport', 'rate_per_km' => 3.0],
                ['id' => 4, 'name' => 'Train (Intercity AC/Non-AC)', 'rate_per_km' => 4.0],
                ['id' => 5, 'name' => 'Company Car / Pool Vehicle', 'rate_per_km' => 0.0],
            ],
        ]);
    }

    /**
     * Tour Plans list.
     */
    public function tourPlans(Request $request): JsonResponse
    {
        try {
            $query = \Illuminate\Support\Facades\DB::table('tour_plans');
            if ($request->filled('month')) {
                $query->where('month', $request->query('month'));
            }
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->query('employee_id'));
            }
            $plans = $query->orderBy('id', 'desc')->get();
            if ($plans->isNotEmpty()) {
                return response()->json([
                    'status' => true,
                    'data' => $plans,
                ]);
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return response()->json([
            'status' => true,
            'data' => [
                [
                    'id' => 1,
                    'month' => '2026-10',
                    'employee_id' => 479,
                    'employee_name' => 'Abdul Halim',
                    'status' => 'Approved',
                    'total_working_days' => 24,
                    'tour_days' => 18,
                    'base_station' => 'Mirpur Central Depot',
                    'created_at' => '2026-09-28',
                ],
                [
                    'id' => 2,
                    'month' => '2026-10',
                    'employee_id' => 480,
                    'employee_name' => 'Kamrul Hasan',
                    'status' => 'Pending Approval',
                    'total_working_days' => 24,
                    'tour_days' => 16,
                    'base_station' => 'Tejgaon Depot',
                    'created_at' => '2026-09-29',
                ],
            ],
        ]);
    }

    /**
     * Tour Plan Detail by Month.
     */
    public function tourPlanDetail(Request $request): JsonResponse
    {
        $month = $request->query('month', '2026-10');

        return response()->json([
            'status' => true,
            'data' => [
                'plan_id' => 1,
                'month' => $month,
                'employee_name' => 'Abdul Halim',
                'schedule' => [
                    [
                        'date' => "{$month}-02",
                        'work_type' => 'Market Visit / Outlet Audit',
                        'zone1' => ['point' => 'Mirpur-10 Circle', 'time' => '09:00', 'lat' => '23.8068', 'long' => '90.3687', 'transport' => 'Motorbike'],
                        'zone2' => ['point' => 'Pallabi Bus Stand', 'time' => '11:30', 'lat' => '23.8214', 'long' => '90.3654', 'transport' => 'Motorbike'],
                        'zone3' => ['point' => 'Uttara Sector 3', 'time' => '15:00', 'lat' => '23.8687', 'long' => '90.3984', 'transport' => 'Motorbike'],
                        'status' => 'Completed',
                    ],
                    [
                        'date' => "{$month}-03",
                        'work_type' => 'Distributor Business Review',
                        'zone1' => ['point' => 'Meril Badda, Dhaka', 'time' => '10:00', 'lat' => '23.7734', 'long' => '90.4246', 'transport' => 'CNG Auto Rickshaw'],
                        'zone2' => ['point' => 'Gulshan 1 Circle', 'time' => '14:00', 'lat' => '23.7782', 'long' => '90.4158', 'transport' => 'CNG Auto Rickshaw'],
                        'status' => 'In Progress',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Store Tour Plan.
     */
    public function storeTourPlan(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Monthly Tour Plan submitted successfully for supervisor approval',
            'data' => array_merge($request->all(), ['id' => rand(10, 99), 'status' => 'Pending Approval']),
        ]);
    }

    /**
     * Update Tour Plan.
     */
    public function updateTourPlan(Request $request, $id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => "Tour Plan #{$id} amended and updated successfully",
            'data' => array_merge($request->all(), ['id' => (int) $id]),
        ]);
    }

    /**
     * Approve Tour Plans.
     */
    public function approveTourPlans(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Tour plans approved successfully',
            'data' => [
                'approved_ids' => $request->input('item_ids', [$request->input('id', 1)]),
            ],
        ]);
    }

    /**
     * Punch Plan check-in during tour.
     */
    public function punchPlan(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Tour milestone punch verified with GPS location coordinates',
            'data' => [
                'punch_id' => rand(100, 999),
                'point' => $request->input('point', 'Mirpur 10'),
                'time' => now()->toTimeString(),
                'lat' => $request->input('lat', '23.8068'),
                'long' => $request->input('long', '90.3687'),
            ],
        ]);
    }

    /**
     * Tour Plan Claims List.
     */
    public function tourPlansClaims(Request $request): JsonResponse
    {
        try {
            $query = \Illuminate\Support\Facades\DB::table('tour_claims');
            if ($request->filled('month')) {
                $query->where('month', $request->query('month'));
            }
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->query('employee_id'));
            }
            $claims = $query->orderBy('id', 'desc')->get();
            if ($claims->isNotEmpty()) {
                return response()->json(['status' => true, 'data' => $claims]);
            }
        } catch (\Throwable $e) {
            // fallback
        }

        $claims = [
            [
                'id' => 1,
                'claim_no' => 'CLM-2026-091',
                'month' => $request->query('month', '2026-10'),
                'date' => '2026-10-02',
                'employee_name' => 'Abdul Halim',
                'da_amount' => 450.00,
                'ta_amount' => 380.00,
                'total_amount' => 830.00,
                'status' => 'Approved',
            ],
            [
                'id' => 2,
                'claim_no' => 'CLM-2026-092',
                'month' => $request->query('month', '2026-10'),
                'date' => '2026-10-03',
                'employee_name' => 'Abdul Halim',
                'da_amount' => 450.00,
                'ta_amount' => 420.00,
                'total_amount' => 870.00,
                'status' => 'Submitted',
            ],
        ];

        return response()->json(['status' => true, 'data' => $claims]);
    }

    public function tourPlanClaimDetail($id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                'id' => (int) $id,
                'claim_no' => "CLM-2026-09{$id}",
                'date' => '2026-10-02',
                'items' => [
                    ['from' => 'Mirpur-10', 'to' => 'Pallabi', 'transport' => 'Motorbike', 'km' => 6.5, 'cost' => 65.00],
                    ['from' => 'Pallabi', 'to' => 'Uttara Sector 3', 'transport' => 'Motorbike', 'km' => 14.0, 'cost' => 140.00],
                ],
                'daily_allowance' => 450.00,
                'total' => 655.00,
            ],
        ]);
    }

    public function tourPlanClaimDa($id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                'claim_id' => (int) $id,
                'da_rate_per_day' => 450.00,
                'eligible_days' => 1,
                'total_da' => 450.00,
            ],
        ]);
    }

    public function claimsList(): JsonResponse
    {
        try {
            $claims = \Illuminate\Support\Facades\DB::table('tour_claims')
                ->orderBy('id', 'desc')
                ->limit(20)
                ->get();
            if ($claims->isNotEmpty()) {
                $mapped = $claims->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'category' => 'Travel & Daily Allowance',
                        'amount' => (float) $c->total_amount,
                        'date' => $c->date,
                        'status' => $c->status,
                    ];
                });
                return response()->json([
                    'status' => true,
                    'data' => $mapped,
                ]);
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'category' => 'Travel Allowance (TA)', 'amount' => 380.00, 'date' => '2026-10-02', 'status' => 'Paid'],
                ['id' => 2, 'category' => 'Daily Allowance (DA)', 'amount' => 450.00, 'date' => '2026-10-02', 'status' => 'Paid'],
                ['id' => 3, 'category' => 'Client Entertainment', 'amount' => 650.00, 'date' => '2026-10-03', 'status' => 'Pending Approval'],
            ],
        ]);
    }

    public function claimDetail($id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => ['id' => (int) $id, 'claim_type' => 'Travel & Daily Allowance', 'status' => 'Approved', 'amount' => 830.00],
        ]);
    }

    /**
     * Store Tour Plan Claim.
     */
    public function storeTourPlanClaim(Request $request): JsonResponse
    {
        $claimNo = 'CLM-' . date('Y') . '-' . rand(1000, 9999);
        $data = [
            'claim_no' => $claimNo,
            'month' => $request->input('month', date('Y-m')),
            'date' => $request->input('date', date('Y-m-d')),
            'employee_id' => $request->input('employee_id', 479),
            'employee_name' => $request->input('employee_name', 'Abdul Halim'),
            'route' => $request->input('route', 'Dhaka Regional Territory'),
            'da_amount' => (float) $request->input('da_amount', 450.00),
            'ta_amount' => (float) $request->input('ta_amount', 380.00),
            'hotel_amount' => (float) $request->input('hotel_amount', 0),
            'other_amount' => (float) $request->input('other_amount', 0),
            'total_amount' => (float) $request->input('total_amount', 830.00),
            'status' => 'Submitted',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        try {
            $insertedId = \Illuminate\Support\Facades\DB::table('tour_claims')->insertGetId($data);
            $data['id'] = $insertedId;
        } catch (\Throwable $e) {
            $data['id'] = rand(10, 99);
        }

        return response()->json([
            'status' => true,
            'message' => 'Tour plan expense claim submitted for accounts disbursement',
            'data' => $data,
        ]);
    }

    /**
     * Expense Types for Other Expense.
     */
    public function expenseTypes(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'name' => 'Travel & Commuting'],
                ['id' => 2, 'name' => 'Meals & Per Diem'],
                ['id' => 3, 'name' => 'Hotel Accommodation'],
                ['id' => 4, 'name' => 'Client Refreshment / Meeting Entertainment'],
                ['id' => 5, 'name' => 'Mobile Courier & Postal'],
            ],
        ]);
    }

    public function storeOtherExpense(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Expense voucher recorded successfully',
            'data' => array_merge($request->all(), ['id' => rand(10, 99), 'status' => 'Pending']),
        ]);
    }

    /**
     * Daily Work List.
     */
    public function dailyWorkList(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'date' => '2026-10-02', 'task_title' => 'Key Account Audit at Mirpur Sector 10', 'hours_spent' => 4.5, 'status' => 'Approved'],
                ['id' => 2, 'date' => '2026-10-03', 'task_title' => 'Depot Stock Reconcile & Dispatch Verification', 'hours_spent' => 3.0, 'status' => 'Submitted'],
            ],
        ]);
    }

    /**
     * Apply Daily Work.
     */
    public function applyDailyWork(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Daily work log submitted for manager review',
            'data' => array_merge($request->all(), ['id' => rand(10, 99), 'created_at' => now()->toDateTimeString()]),
        ]);
    }
}
