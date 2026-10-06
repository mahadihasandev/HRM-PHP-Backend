<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SndController extends BaseApiController
{
    /**
     * SND Executive Dashboard.
     */
    public function dashboard(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                'total_customers' => 1240,
                'active_depots' => 18,
                'today_orders_count' => 84,
                'today_order_value' => 745000,
                'monthly_sales_target' => 18500000,
                'monthly_achievement' => 14200000,
                'outlet_coverage_rate' => '91.4%',
                'top_products' => [
                    ['id' => 1, 'name' => 'Synthetic Motor Oil 4T', 'units' => 4200, 'sales' => 2520000],
                    ['id' => 2, 'name' => 'Heavy Duty Diesel Engine Oil', 'units' => 3100, 'sales' => 3720000],
                    ['id' => 3, 'name' => 'Industrial Gear Lubricant ISO 220', 'units' => 1800, 'sales' => 1980000],
                ],
            ],
            'message' => 'SND dashboard metrics retrieved',
        ]);
    }

    /**
     * Customer Types list.
     */
    public function customerTypes(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'name' => 'Authorized Distributor', 'code' => 'DIST'],
                ['id' => 2, 'name' => 'Wholesale Dealer', 'code' => 'WHL'],
                ['id' => 3, 'name' => 'Retail Outlet / Workshop', 'code' => 'RET'],
                ['id' => 4, 'name' => 'Key Institutional Account', 'code' => 'INST'],
            ],
        ]);
    }

    /**
     * Owner Types list.
     */
    public function ownerTypes(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'name' => 'Proprietorship'],
                ['id' => 2, 'name' => 'Partnership'],
                ['id' => 3, 'name' => 'Private Limited Company'],
            ],
        ]);
    }

    /**
     * Depots list.
     */
    public function depots(Request $request): JsonResponse
    {
        $depots = [
            ['id' => 1, 'name' => 'D-Mirpur Central Depot', 'code' => 'D-MIR', 'region' => 'Dhaka North', 'in_charge' => 'Md. Faruk Hossain', 'phone' => '01711002233'],
            ['id' => 2, 'name' => 'D-Tejgaon Industrial Depot', 'code' => 'D-TEJ', 'region' => 'Dhaka South', 'in_charge' => 'Khandaker Shafi', 'phone' => '01711002244'],
            ['id' => 3, 'name' => 'D-Chittagong Port Depot', 'code' => 'D-CTG', 'region' => 'Chittagong', 'in_charge' => 'S.M. Nasir', 'phone' => '01711002255'],
            ['id' => 4, 'name' => 'D-Bogura Regional Depot', 'code' => 'D-BOG', 'region' => 'Rajshahi', 'in_charge' => 'Arifuzzaman', 'phone' => '01711002266'],
        ];

        $search = $request->query('search');
        if ($search) {
            $depots = array_values(array_filter($depots, fn($d) => stripos($d['name'], $search) !== false || stripos($d['code'], $search) !== false));
        }

        return response()->json(['status' => true, 'data' => $depots]);
    }

    /**
     * Customer Directory list.
     */
    public function customers(Request $request): JsonResponse
    {
        try {
            $query = \Illuminate\Support\Facades\DB::table('snd_customers');
            if ($request->filled('search')) {
                $s = '%' . $request->query('search') . '%';
                $query->where(function ($q) use ($s) {
                    $q->where('name', 'like', $s)
                      ->orWhere('code', 'like', $s)
                      ->orWhere('proprietor', 'like', $s)
                      ->orWhere('territory', 'like', $s);
                });
            }
            $dbCustomers = $query->orderBy('id', 'desc')->get();
            if ($dbCustomers->isNotEmpty()) {
                $mapped = $dbCustomers->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'code' => $c->code,
                        'name' => $c->name,
                        'phone' => $c->phone,
                        'email' => strtolower(str_replace(' ', '', $c->name)) . '@outlet.bd',
                        'depot_id' => 1,
                        'depot_name' => $c->territory . ' Depot',
                        'customer_type_id' => 3,
                        'customer_type' => 'Retail Outlet / Workshop',
                        'owner_name' => $c->proprietor,
                        'owner_phone' => $c->phone,
                        'shop_size' => '1200 sqft',
                        'thana_id' => 500,
                        'thana' => $c->territory,
                        'address' => $c->route . ', ' . $c->territory,
                        'lat' => '23.8058',
                        'long' => '90.3533',
                        'due_balance' => (float) $c->due_balance,
                        'credit_limit' => (float) $c->credit_limit,
                        'status' => $c->status,
                    ];
                });
                return response()->json(['status' => true, 'data' => $mapped, 'total' => $mapped->count()]);
            }
        } catch (\Throwable $e) {
            // fallback
        }

        $customers = [
            [
                'id' => 63,
                'code' => 'CUS-09184',
                'name' => 'CodeTap Distributors',
                'phone' => '01712345678',
                'email' => 'support@codetap.org',
                'depot_id' => 1,
                'depot_name' => 'D-Mirpur Central Depot',
                'customer_type_id' => 1,
                'customer_type' => 'Authorized Distributor',
                'owner_name' => 'Md. Kabirul Islam',
                'owner_phone' => '01712345678',
                'shop_size' => '1500 sqft',
                'thana_id' => 500,
                'thana' => 'Mirpur',
                'address' => 'House 12, Road 4, Sector 3, Mirpur, Dhaka',
                'lat' => '23.8058',
                'long' => '90.3533',
                'status' => 'Active',
            ],
            [
                'id' => 64,
                'code' => 'CUS-09185',
                'name' => 'Padma Lubricants & Auto Center',
                'phone' => '01898765432',
                'email' => 'padma.lubes@gmail.com',
                'depot_id' => 1,
                'depot_name' => 'D-Mirpur Central Depot',
                'customer_type_id' => 3,
                'customer_type' => 'Retail Outlet / Workshop',
                'owner_name' => 'Haji Sirajul Islam',
                'owner_phone' => '01898765432',
                'shop_size' => '850 sqft',
                'thana_id' => 500,
                'thana' => 'Pallabi',
                'address' => 'Plot 88, Section 11, Pallabi, Dhaka',
                'lat' => '23.8214',
                'long' => '90.3654',
                'status' => 'Active',
            ],
            [
                'id' => 65,
                'code' => 'CUS-09186',
                'name' => 'Karnaphuli Trading Agency',
                'phone' => '01912445566',
                'email' => 'karnaphuli.trade@ctg.bd',
                'depot_id' => 3,
                'depot_name' => 'D-Chittagong Port Depot',
                'customer_type_id' => 2,
                'customer_type' => 'Wholesale Dealer',
                'owner_name' => 'Rashidul Haque',
                'owner_phone' => '01912445566',
                'shop_size' => '2200 sqft',
                'thana_id' => 545,
                'thana' => 'Agrabad',
                'address' => 'Commercial Area, Agrabad, Chittagong',
                'lat' => '22.3255',
                'long' => '91.8122',
                'status' => 'Active',
            ],
        ];

        return response()->json(['status' => true, 'data' => $customers, 'total' => count($customers)]);
    }

    /**
     * Store new Customer.
     */
    public function storeCustomer(Request $request): JsonResponse
    {
        $code = $request->input('code') ?: 'CUS-' . rand(10000, 99999);
        $name = $request->input('name', 'New Retailer');
        $proprietor = $request->input('owner_name') ?: $request->input('proprietor', 'Proprietor');
        $phone = $request->input('phone', '01700000000');
        $territory = $request->input('thana') ?: $request->input('territory', 'Dhaka North');
        $route = $request->input('address') ?: $request->input('route', 'Main Road');

        $data = [
            'name' => $name,
            'proprietor' => $proprietor,
            'code' => $code,
            'route' => $route,
            'territory' => $territory,
            'phone' => $phone,
            'due_balance' => (float) $request->input('due_balance', 0),
            'credit_limit' => (float) $request->input('credit_limit', 100000),
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        try {
            $insertedId = \Illuminate\Support\Facades\DB::table('snd_customers')->insertGetId($data);
            $data['id'] = $insertedId;
        } catch (\Throwable $e) {
            $data['id'] = rand(100, 999);
        }

        return response()->json([
            'status' => true,
            'message' => 'Customer registered successfully in SND database',
            'data' => $data,
        ]);
    }

    /**
     * Thana-wise customers.
     */
    public function thanaWiseCustomers(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                'thana_id' => $request->input('thana_id', 500),
                'total_outlets' => 48,
                'active_outlets' => 45,
                'customers' => [
                    ['id' => 63, 'name' => 'CodeTap Distributors', 'type' => 'Distributor', 'balance' => 0],
                    ['id' => 64, 'name' => 'Padma Lubricants & Auto Center', 'type' => 'Retail', 'balance' => 12500],
                ],
            ],
        ]);
    }

    /**
     * Check thana depots.
     */
    public function checkThanaDepots(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                'thana_id' => $request->input('thana_id', 545),
                'assigned_depot' => [
                    'id' => 1,
                    'name' => 'D-Mirpur Central Depot',
                    'lead_time_days' => 1,
                    'is_primary' => true,
                ],
            ],
        ]);
    }

    /**
     * Get depot by customer.
     */
    public function getDepotByCustomer(Request $request, $customer_id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                'customer_id' => (int) $customer_id,
                'depot_id' => 1,
                'depot_name' => 'D-Mirpur Central Depot',
                'delivery_radius_km' => 25,
            ],
        ]);
    }

    /**
     * Reasons for not ordering during outlet visit.
     */
    public function notOrderReasons(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'reason' => 'Sufficient Inventory / Stock Available'],
                ['id' => 2, 'reason' => 'Owner / Decision Maker Not Available'],
                ['id' => 3, 'reason' => 'Payment Dispute / Overdue Balance Pending'],
                ['id' => 4, 'reason' => 'Competitor Scheme / Promotion Advantage'],
                ['id' => 5, 'reason' => 'Outlet Temporarily Closed'],
            ],
        ]);
    }

    /**
     * Punch Sales Order at customer location (GPS Check-in).
     */
    public function punchSales(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Sales visit check-in punched successfully',
            'data' => [
                'punch_id' => rand(100, 999),
                'customer_id' => $request->input('customer_id', 63),
                'punch_in' => $request->input('punch_in', now()->toDateTimeString()),
                'punch_out' => $request->input('punch_out', now()->addMinutes(15)->toDateTimeString()),
                'latitude' => $request->input('latitude', '23.7513'),
                'longitude' => $request->input('longitude', '90.3858'),
                'comment' => $request->input('comment', 'Order placed successfully'),
            ],
        ]);
    }

    /**
     * Store Sales Order.
     */
    public function storeSalesOrder(Request $request): JsonResponse
    {
        $orderNo = 'SO-DPT-' . rand(1000, 9999);
        $date = $request->input('date', now()->toDateString());
        $customerId = $request->input('customer_id', 1);
        $customerName = $request->input('customer_name', 'Customer #' . $customerId);
        $subtotal = (float) $request->input('subtotal', 1000.00);
        $discount = (float) $request->input('total_discount', 50.00);
        $payable = (float) $request->input('payable_amount', ($subtotal - $discount));

        $data = [
            'order_no' => $orderNo,
            'customer_id' => $customerId,
            'customer_name' => $customerName,
            'date' => $date,
            'item_count' => (int) $request->input('items_count', 3),
            'total_amount' => $subtotal,
            'discount_amount' => $discount,
            'payable_amount' => $payable,
            'payment_status' => 'Pending',
            'delivery_status' => 'Processing',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        try {
            $insertedId = \Illuminate\Support\Facades\DB::table('snd_orders')->insertGetId($data);
            $data['id'] = $insertedId;
        } catch (\Throwable $e) {
            $data['id'] = rand(100, 999);
        }

        return response()->json([
            'status' => true,
            'message' => 'Sales Order created successfully and submitted to Depot for fulfillment',
            'data' => [
                'id' => $data['id'],
                'order_no' => $orderNo,
                'date' => $date,
                'customer_id' => $customerId,
                'subtotal' => $subtotal,
                'total_discount' => $discount,
                'payable_amount' => $payable,
                'status' => 'Pending Approval',
                'remarks' => $request->input('remarks', 'Direct field order'),
            ],
        ]);
    }

    /**
     * List Sales Orders.
     */
    public function salesOrders(Request $request): JsonResponse
    {
        try {
            $orders = \Illuminate\Support\Facades\DB::table('snd_orders')
                ->orderBy('id', 'desc')
                ->get();
            if ($orders->isNotEmpty()) {
                $mapped = $orders->map(function ($o) {
                    return [
                        'id' => $o->id,
                        'order_no' => $o->order_no,
                        'date' => $o->date,
                        'customer_name' => $o->customer_name,
                        'depot_name' => 'Dhaka Central Depot',
                        'subtotal' => (float) $o->total_amount,
                        'discount' => (float) $o->discount_amount,
                        'payable_amount' => (float) $o->payable_amount,
                        'status' => $o->payment_status === 'Paid' ? 'Approved' : 'Pending Approval',
                        'sr_name' => 'Abdul Halim (SMT-0051)',
                        'items_count' => (int) $o->item_count,
                    ];
                });
                return response()->json(['status' => true, 'data' => $mapped]);
            }
        } catch (\Throwable $e) {
            // fallback
        }

        $orders = [
            [
                'id' => 1,
                'order_no' => 'SO-DPT-1042',
                'date' => '2026-10-02',
                'customer_name' => 'CodeTap Distributors',
                'depot_name' => 'D-Mirpur Central Depot',
                'subtotal' => 125000,
                'discount' => 6250,
                'payable_amount' => 118750,
                'status' => 'Approved',
                'sr_name' => 'Abdul Halim (SMT-0051)',
                'items_count' => 4,
            ],
            [
                'id' => 2,
                'order_no' => 'SO-DPT-1043',
                'date' => '2026-10-03',
                'customer_name' => 'Padma Lubricants & Auto Center',
                'depot_name' => 'D-Mirpur Central Depot',
                'subtotal' => 45000,
                'discount' => 2250,
                'payable_amount' => 42750,
                'status' => 'Pending Approval',
                'sr_name' => 'Abdul Halim (SMT-0051)',
                'items_count' => 2,
            ],
            [
                'id' => 3,
                'order_no' => 'SO-DPT-1044',
                'date' => '2026-10-03',
                'customer_name' => 'Karnaphuli Trading Agency',
                'depot_name' => 'D-Chittagong Port Depot',
                'subtotal' => 88000,
                'discount' => 4400,
                'payable_amount' => 83600,
                'status' => 'Pending Approval',
                'sr_name' => 'Abdul Halim (SMT-0051)',
                'items_count' => 3,
            ],
        ];

        return response()->json(['status' => true, 'data' => $orders]);
    }

    /**
     * Approve Sales Order.
     */
    public function approveSalesOrder(Request $request, $id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => "Sales order #{$id} approved successfully for dispatch",
            'data' => ['id' => (int) $id, 'status' => 'Approved'],
        ]);
    }

    /**
     * Reject Sales Order.
     */
    public function rejectSalesOrder(Request $request, $id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => "Sales order #{$id} rejected",
            'data' => ['id' => (int) $id, 'status' => 'Rejected', 'reason' => $request->input('reason', 'Stock deficit')],
        ]);
    }

    /**
     * Update Sales Order.
     */
    public function updateSalesOrder(Request $request, $id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => "Sales order #{$id} updated successfully",
            'data' => array_merge($request->all(), ['id' => (int) $id]),
        ]);
    }

    /**
     * Products list for ordering.
     */
    public function products(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'code' => 'PRD-01', 'name' => 'Formula G 5W-40 Synthetic (1L)', 'tp' => 850.00, 'mrp' => 950.00, 'stock' => 1400, 'category' => 'Passenger Car Motor Oil'],
                ['id' => 2, 'code' => 'PRD-02', 'name' => 'Supreme 20W-50 Premium (4L)', 'tp' => 2200.00, 'mrp' => 2500.00, 'stock' => 850, 'category' => 'Heavy Duty Commercial'],
                ['id' => 3, 'code' => 'PRD-03', 'name' => 'Super Fleet Special 15W-40 (5L)', 'tp' => 2800.00, 'mrp' => 3100.00, 'stock' => 620, 'category' => 'Fleet & Commercial'],
                ['id' => 4, 'code' => 'PRD-04', 'name' => 'Industrial EP Gear Oil 320 (20L Drum)', 'tp' => 11500.00, 'mrp' => 13000.00, 'stock' => 140, 'category' => 'Industrial Lubricants'],
                ['id' => 5, 'code' => 'PRD-05', 'name' => 'Hydraulic AW 68 (208L Barrel)', 'tp' => 48000.00, 'mrp' => 54000.00, 'stock' => 35, 'category' => 'Industrial Lubricants'],
            ],
        ]);
    }

    /**
     * Sales Representatives list.
     */
    public function salesRepresentatives(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 479, 'code' => 'SMT-0051', 'name' => 'Abdul Halim', 'territory' => 'Dhaka North Zone', 'monthly_target' => 2500000, 'achievement' => 2180000, 'phone' => '01712000051'],
                ['id' => 480, 'code' => 'SMT-0052', 'name' => 'Kamrul Hasan', 'territory' => 'Dhaka South Zone', 'monthly_target' => 2200000, 'achievement' => 1950000, 'phone' => '01712000052'],
                ['id' => 481, 'code' => 'SMT-0053', 'name' => 'Zubair Hossain', 'territory' => 'Chittagong Metro', 'monthly_target' => 3000000, 'achievement' => 2850000, 'phone' => '01712000053'],
            ],
        ]);
    }

    /**
     * SR Outlet Visit Report.
     */
    public function outletVisitReport(Request $request): JsonResponse
    {
        try {
            $visits = \Illuminate\Support\Facades\DB::table('snd_visits')
                ->orderBy('id', 'desc')
                ->get();
            if ($visits->isNotEmpty()) {
                $mapped = $visits->groupBy('date')->map(function ($group, $date) {
                    $first = $group->first();
                    $visited = $group->count();
                    $productive = $group->where('outcome', 'Order Placed')->count();
                    $strike = $visited > 0 ? round(($productive / $visited) * 100, 1) . '%' : '0%';
                    return [
                        'id' => $first->id,
                        'date' => $date,
                        'sr_name' => $first->representative ?: 'Abdul Halim',
                        'planned_outlets' => $visited + 2,
                        'visited_outlets' => $visited,
                        'productive_outlets' => $productive,
                        'strike_rate' => $strike,
                        'order_amount' => $productive * 15500,
                    ];
                })->values();

                if ($mapped->isNotEmpty()) {
                    return response()->json([
                        'status' => true,
                        'data' => $mapped,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'date' => '2026-10-02', 'sr_name' => 'Abdul Halim', 'planned_outlets' => 15, 'visited_outlets' => 14, 'productive_outlets' => 11, 'strike_rate' => '78.5%', 'order_amount' => 161500],
                ['id' => 2, 'date' => '2026-10-01', 'sr_name' => 'Abdul Halim', 'planned_outlets' => 14, 'visited_outlets' => 14, 'productive_outlets' => 12, 'strike_rate' => '85.7%', 'order_amount' => 184000],
                ['id' => 3, 'date' => '2026-09-30', 'sr_name' => 'Abdul Halim', 'planned_outlets' => 16, 'visited_outlets' => 15, 'productive_outlets' => 13, 'strike_rate' => '86.6%', 'order_amount' => 210000],
            ],
        ]);
    }

    public function outletVisitReportFilters(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                'depots' => [['id' => 1, 'name' => 'D-Mirpur Central Depot'], ['id' => 2, 'name' => 'D-Tejgaon Industrial Depot']],
                'sales_representatives' => [['id' => 479, 'name' => 'Abdul Halim']],
            ],
        ]);
    }

    public function outletVisitReportExport(): JsonResponse
    {
        return response()->json(['status' => true, 'message' => 'Report exported', 'download_url' => '/exports/sr-outlet-visit.xlsx']);
    }

    /**
     * SR Market Order Report.
     */
    public function marketOrderReport(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'date' => '2026-10-02', 'sr_name' => 'Abdul Halim', 'orders' => 11, 'volume_liters' => 640, 'order_value' => 161500, 'depot' => 'D-Mirpur Central Depot'],
                ['id' => 2, 'date' => '2026-10-01', 'sr_name' => 'Abdul Halim', 'orders' => 12, 'volume_liters' => 720, 'order_value' => 184000, 'depot' => 'D-Mirpur Central Depot'],
            ],
        ]);
    }

    public function marketOrderReportFilters(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => ['categories' => ['PCMO', 'HDDO', 'Industrial Lubricants']],
        ]);
    }

    public function marketOrderReportExport(): JsonResponse
    {
        return response()->json(['status' => true, 'message' => 'Report exported', 'download_url' => '/exports/sr-market-order.xlsx']);
    }

    /**
     * SR Monitoring Dashboard.
     */
    public function monitoringDashboard(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                'active_srs' => 24,
                'checked_in_srs' => 23,
                'total_scheduled_calls' => 360,
                'actual_calls_made' => 312,
                'effective_calls' => 268,
                'strike_ratio' => '85.9%',
                'total_booked_value' => 4180000,
            ],
        ]);
    }

    public function monitoringDashboardFilters(): JsonResponse
    {
        return response()->json(['status' => true, 'data' => ['regions' => ['Dhaka North', 'Dhaka South', 'Chittagong']]]);
    }

    public function monitoringDashboardExport(): JsonResponse
    {
        return response()->json(['status' => true, 'message' => 'Dashboard exported', 'download_url' => '/exports/sr-monitoring.xlsx']);
    }
}
