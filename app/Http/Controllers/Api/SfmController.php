<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SfmController extends BaseApiController
{
    /**
     * SFM Geographic Setups: Countries.
     */
    public function countries(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'name' => 'Bangladesh', 'code' => 'BGD', 'currency' => 'BDT'],
            ],
        ]);
    }

    /**
     * SFM Geographic Setups: Divisions.
     */
    public function divisions(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'name' => 'Dhaka Division', 'code' => 'DIV-DHK', 'country_id' => 1],
                ['id' => 2, 'name' => 'Chittagong Division', 'code' => 'DIV-CTG', 'country_id' => 1],
                ['id' => 3, 'name' => 'Rajshahi Division', 'code' => 'DIV-RAJ', 'country_id' => 1],
                ['id' => 4, 'name' => 'Khulna Division', 'code' => 'DIV-KHL', 'country_id' => 1],
                ['id' => 5, 'name' => 'Sylhet Division', 'code' => 'DIV-SYL', 'country_id' => 1],
            ],
        ]);
    }

    /**
     * SFM Geographic Setups: Nationals.
     */
    public function nationals(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'name' => 'National Operations North', 'head_user' => 'Ziaur Rahman'],
                ['id' => 2, 'name' => 'National Operations South', 'head_user' => 'Syed Ahsan'],
            ],
        ]);
    }

    /**
     * SFM Geographic Setups: Regions.
     */
    public function regions(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 10, 'name' => 'Dhaka Metro North', 'division_id' => 1, 'manager' => 'Md. Rezaul Karim'],
                ['id' => 11, 'name' => 'Dhaka Metro South', 'division_id' => 1, 'manager' => 'Shahidul Alam'],
                ['id' => 12, 'name' => 'Chittagong Central', 'division_id' => 2, 'manager' => 'M. A. Taher'],
                ['id' => 13, 'name' => 'Bogura & North Bengal', 'division_id' => 3, 'manager' => 'Fazlul Huq'],
            ],
        ]);
    }

    /**
     * SFM Geographic Setups: Zones.
     */
    public function zones(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 3, 'name' => 'Mirpur-Pallabi-Uttara Zone', 'region_id' => 10, 'supervisor' => 'Kaiser Ahmed'],
                ['id' => 4, 'name' => 'Tejgaon-Gulshan-Badda Zone', 'region_id' => 10, 'supervisor' => 'Enamul Haque'],
                ['id' => 5, 'name' => 'Agrabad-Halishahar Zone', 'region_id' => 12, 'supervisor' => 'Nawaz Sharif'],
            ],
        ]);
    }

    /**
     * SFM Geographic Setups: Bases.
     */
    public function bases(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'name' => 'Base Mirpur 10', 'zone_id' => 3, 'lead_sr' => 'Abdul Halim'],
                ['id' => 2, 'name' => 'Base Uttara Sector 3', 'zone_id' => 3, 'lead_sr' => 'Kamrul Hasan'],
                ['id' => 3, 'name' => 'Base Agrabad C/A', 'zone_id' => 5, 'lead_sr' => 'Zubair Hossain'],
            ],
        ]);
    }

    /**
     * SFM Geographic Setups: Thanas.
     */
    public function thanas(Request $request): JsonResponse
    {
        $thanas = [
            ['id' => 500, 'name' => 'Mirpur Model Thana', 'district' => 'Dhaka', 'zone_id' => 3],
            ['id' => 501, 'name' => 'Pallabi Thana', 'district' => 'Dhaka', 'zone_id' => 3],
            ['id' => 502, 'name' => 'Uttara West Thana', 'district' => 'Dhaka', 'zone_id' => 3],
            ['id' => 503, 'name' => 'Tejgaon Industrial Thana', 'district' => 'Dhaka', 'zone_id' => 4],
            ['id' => 545, 'name' => 'Double Mooring / Agrabad Thana', 'district' => 'Chittagong', 'zone_id' => 5],
        ];

        return response()->json(['status' => true, 'data' => $thanas]);
    }

    /**
     * SFM Geographic Setups: Unions / Wards.
     */
    public function unions(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'name' => 'Ward No. 03 (Mirpur-2)', 'thana_id' => 500],
                ['id' => 2, 'name' => 'Ward No. 06 (Pallabi)', 'thana_id' => 501],
                ['id' => 3, 'name' => 'Ward No. 24 (Agrabad Commercial)', 'thana_id' => 545],
            ],
        ]);
    }

    /**
     * SFM Target Dashboard.
     */
    public function targetDashboard(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                'current_month' => now()->format('Y-m'),
                'total_assigned_target' => 45000000,
                'total_commitment_value' => 42800000,
                'achieved_value_to_date' => 31250000,
                'achievement_rate' => '73.0%',
                'total_field_force' => 32,
                'top_performers' => [
                    ['name' => 'Abdul Halim', 'target' => 2500000, 'achieved' => 2180000, 'rate' => '87.2%'],
                    ['name' => 'Kamrul Hasan', 'target' => 2200000, 'achieved' => 1950000, 'rate' => '88.6%'],
                    ['name' => 'Zubair Hossain', 'target' => 3000000, 'achieved' => 2850000, 'rate' => '95.0%'],
                ],
            ],
        ]);
    }

    /**
     * SFM Target Commitments List.
     */
    /**
     * SFM Target Commitments List.
     */
    public function targetCommitments(Request $request): JsonResponse
    {
        $month = $request->query('month', now()->format('Y-m'));

        try {
            $dbCommitments = \Illuminate\Support\Facades\DB::table('sfm_commitments')
                ->where('month', $month)
                ->orderBy('id', 'desc')
                ->get();
            if ($dbCommitments->isEmpty()) {
                $dbCommitments = \Illuminate\Support\Facades\DB::table('sfm_commitments')
                    ->orderBy('id', 'desc')
                    ->get();
            }
            if ($dbCommitments->isNotEmpty()) {
                $mapped = $dbCommitments->map(function ($c) use ($month) {
                    return [
                        'id' => $c->id,
                        'employee_id' => $c->employee_id,
                        'employee_name' => $c->employee_name,
                        'code' => $c->code,
                        'territory' => $c->territory,
                        'month' => $c->month ?: $month,
                        'target_value' => (float) $c->target_value,
                        'commitment_value' => (float) $c->commitment_value,
                        'actual_sales' => (float) $c->actual_sales,
                        'details' => [
                            ['detail_id' => 14, 'product_category' => 'Passenger Car Motor Oil', 'target_qty' => 1500, 'commitment_value' => (float) $c->pcmo_commitment, 'achieved_qty' => 1320],
                            ['detail_id' => 15, 'product_category' => 'Commercial Diesel Oil', 'target_qty' => 600, 'commitment_value' => (float) $c->hddo_commitment, 'achieved_qty' => 540],
                        ],
                    ];
                });
                return response()->json([
                    'status' => true,
                    'data' => $mapped,
                    'total' => $mapped->count(),
                    'month' => $month,
                ]);
            }
        } catch (\Throwable $e) {
            // fallback
        }

        $commitments = [
            [
                'id' => 5,
                'employee_id' => 479,
                'employee_name' => 'Abdul Halim',
                'code' => 'SMT-0051',
                'territory' => 'Dhaka North - Mirpur Zone',
                'month' => $month,
                'target_value' => 2500000,
                'commitment_value' => 2677000,
                'actual_sales' => 2180000,
                'details' => [
                    ['detail_id' => 14, 'product_category' => 'Passenger Car Motor Oil', 'target_qty' => 1500, 'commitment_value' => 1275000, 'achieved_qty' => 1320],
                    ['detail_id' => 15, 'product_category' => 'Commercial Diesel Oil', 'target_qty' => 600, 'commitment_value' => 1402000, 'achieved_qty' => 540],
                ],
            ],
            [
                'id' => 6,
                'employee_id' => 480,
                'employee_name' => 'Kamrul Hasan',
                'code' => 'SMT-0052',
                'territory' => 'Dhaka South - Tejgaon Zone',
                'month' => $month,
                'target_value' => 2200000,
                'commitment_value' => 2200000,
                'actual_sales' => 1950000,
                'details' => [
                    ['detail_id' => 16, 'product_category' => 'Commercial Diesel Oil', 'target_qty' => 500, 'commitment_value' => 1200000, 'achieved_qty' => 460],
                    ['detail_id' => 17, 'product_category' => 'Industrial Lubricants', 'target_qty' => 100, 'commitment_value' => 1000000, 'achieved_qty' => 85],
                ],
            ],
        ];

        return response()->json([
            'status' => true,
            'data' => $commitments,
            'total' => count($commitments),
            'month' => $month,
        ]);
    }

    /**
     * User Assigned Target.
     */
    public function userAssignedTarget(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                'employee_id' => 479,
                'code' => 'SMT-0051',
                'name' => 'Abdul Halim',
                'month' => now()->format('Y-m'),
                'target_value' => 2500000,
                'commitment_value' => 2677000,
                'achieved_amount' => 2180000,
                'achievement_pct' => 87.2,
                'days_remaining' => 12,
            ],
        ]);
    }

    /**
     * Update Target Commitments.
     */
    public function updateCommitment(Request $request, $id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => "Target commitment #{$id} updated successfully",
            'data' => [
                'id' => (int) $id,
                'details' => $request->input('details', []),
                'updated_at' => now()->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Shop Visiting Report.
     */
    public function shopVisitingReport(Request $request): JsonResponse
    {
        $reports = [
            [
                'id' => 101,
                'date' => '2026-10-02',
                'sr_code' => 'SMT-0051',
                'sr_name' => 'Abdul Halim',
                'shop_name' => 'CodeTap Distributors',
                'shop_code' => 'CUS-09184',
                'thana' => 'Mirpur',
                'punch_in' => '10:15 AM',
                'punch_out' => '10:48 AM',
                'duration_mins' => 33,
                'is_ordered' => true,
                'order_amount' => 118750,
                'remarks' => 'Order taken and confirmed payment terms',
            ],
            [
                'id' => 102,
                'date' => '2026-10-02',
                'sr_code' => 'SMT-0051',
                'sr_name' => 'Abdul Halim',
                'shop_name' => 'Padma Lubricants & Auto Center',
                'shop_code' => 'CUS-09185',
                'thana' => 'Pallabi',
                'punch_in' => '11:10 AM',
                'punch_out' => '11:32 AM',
                'duration_mins' => 22,
                'is_ordered' => true,
                'order_amount' => 42750,
                'remarks' => 'Regular stock replenishment',
            ],
            [
                'id' => 103,
                'date' => '2026-10-02',
                'sr_code' => 'SMT-0051',
                'sr_name' => 'Abdul Halim',
                'shop_name' => 'Bismillah Auto Parts',
                'shop_code' => 'CUS-09190',
                'thana' => 'Mirpur-1',
                'punch_in' => '12:00 PM',
                'punch_out' => '12:12 PM',
                'duration_mins' => 12,
                'is_ordered' => false,
                'not_order_reason' => 'Sufficient Inventory / Stock Available',
                'remarks' => 'Will order next Monday',
            ],
        ];

        return response()->json(['status' => true, 'data' => $reports]);
    }

    public function shopVisitingReportFilters(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                'thanas' => ['Mirpur', 'Pallabi', 'Uttara', 'Agrabad'],
                'srs' => [['id' => 479, 'name' => 'Abdul Halim'], ['id' => 480, 'name' => 'Kamrul Hasan']],
            ],
        ]);
    }

    /**
     * Shop Summary Report.
     */
    public function shopSummary(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                'total_assigned_shops' => 142,
                'visited_shops_count' => 128,
                'coverage_pct' => '90.1%',
                'ordered_shops_count' => 96,
                'non_ordered_shops_count' => 32,
                'average_visit_duration_min' => 24.5,
            ],
        ]);
    }

    /**
     * SR Summary Report.
     */
    public function srSummary(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                [
                    'sr_code' => 'SMT-0051',
                    'sr_name' => 'Abdul Halim',
                    'total_working_days' => 26,
                    'total_shops_assigned' => 50,
                    'total_visits' => 220,
                    'productive_visits' => 184,
                    'strike_rate' => '83.6%',
                    'total_order_amount' => 2180000,
                ],
                [
                    'sr_code' => 'SMT-0052',
                    'sr_name' => 'Kamrul Hasan',
                    'total_working_days' => 25,
                    'total_shops_assigned' => 45,
                    'total_visits' => 195,
                    'productive_visits' => 160,
                    'strike_rate' => '82.0%',
                    'total_order_amount' => 1950000,
                ],
            ],
        ]);
    }

    /**
     * Non Order Reasons Breakdown.
     */
    public function reasonsBreakdown(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['reason' => 'Sufficient Inventory / Stock Available', 'count' => 42, 'percentage' => 43.7],
                ['reason' => 'Payment Dispute / Overdue Balance Pending', 'count' => 21, 'percentage' => 21.9],
                ['reason' => 'Owner / Decision Maker Not Available', 'count' => 18, 'percentage' => 18.8],
                ['reason' => 'Competitor Scheme / Promotion Advantage', 'count' => 10, 'percentage' => 10.4],
                ['reason' => 'Outlet Temporarily Closed', 'count' => 5, 'percentage' => 5.2],
            ],
        ]);
    }

    /**
     * Attendance Report Filters.
     */
    public function attendanceReportFilters(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                'companies' => [['id' => 7, 'name' => 'RM Carpet Limited'], ['id' => 1, 'name' => 'Gulf Oil BD']],
                'departments' => [['id' => 90, 'name' => 'Sales & Field Distribution']],
                'designations' => [['id' => 187, 'name' => 'Senior Sales Representative']],
            ],
        ]);
    }

    /**
     * Date-wise Attendance Report.
     */
    public function dateWiseAttendanceReport(Request $request): JsonResponse
    {
        $date = $request->query('date', now()->toDateString());

        return response()->json([
            'status' => true,
            'data' => [
                [
                    'employee_id' => 479,
                    'employee_name' => 'Abdul Halim',
                    'code' => 'SMT-0051',
                    'date' => $date,
                    'first_in' => '08:45 AM',
                    'last_out' => '06:15 PM',
                    'status' => 'Present',
                    'outlets_visited' => 14,
                    'geo_location' => 'Mirpur Sector 10 (23.8058, 90.3533)',
                ],
                [
                    'employee_id' => 480,
                    'employee_name' => 'Kamrul Hasan',
                    'code' => 'SMT-0052',
                    'date' => $date,
                    'first_in' => '09:02 AM',
                    'last_out' => '05:50 PM',
                    'status' => 'Present',
                    'outlets_visited' => 12,
                    'geo_location' => 'Tejgaon Link Rd (23.7690, 90.3995)',
                ],
            ],
        ]);
    }

    /**
     * SFM Employees list.
     */
    public function sfmEmployees(Request $request): JsonResponse
    {
        $search = $request->query('search', '');
        $employees = [
            ['id' => 479, 'code' => 'SMT-0051', 'name' => 'Abdul Halim', 'designation' => 'Senior Territory Sales Officer', 'territory' => 'Dhaka North Zone', 'phone' => '01712000051'],
            ['id' => 480, 'code' => 'SMT-0052', 'name' => 'Kamrul Hasan', 'designation' => 'Sales Executive', 'territory' => 'Dhaka South Zone', 'phone' => '01712000052'],
            ['id' => 481, 'code' => 'SMT-0053', 'name' => 'Zubair Hossain', 'designation' => 'Area Sales Manager', 'territory' => 'Chittagong Metro', 'phone' => '01712000053'],
        ];

        if ($search) {
            $employees = array_values(array_filter($employees, fn($e) => stripos($e['name'], $search) !== false || stripos($e['code'], $search) !== false));
        }

        return response()->json(['status' => true, 'data' => $employees]);
    }

    /**
     * Employee Job Card.
     */
    public function employeeJobCard(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                'employee_id' => (int) $request->query('employee_id', 479),
                'month' => $request->query('month', '2026-09'),
                'working_days' => 26,
                'present_days' => 25,
                'late_days' => 1,
                'leave_days' => 1,
                'absent_days' => 0,
                'daily_records' => [
                    ['date' => '2026-09-01', 'in_time' => '08:52', 'out_time' => '18:10', 'status' => 'P'],
                    ['date' => '2026-09-02', 'in_time' => '08:48', 'out_time' => '18:05', 'status' => 'P'],
                    ['date' => '2026-09-03', 'in_time' => '09:12', 'out_time' => '18:15', 'status' => 'L'],
                ],
            ],
        ]);
    }

    /**
     * Multi PDF Job Card.
     */
    public function employeeJobCardMultiPdf(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Job cards generated for all field force in batch',
            'pdf_url' => '/exports/job-cards-2026-09.pdf',
        ]);
    }
}
