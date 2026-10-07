<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AccountingController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\FactoryController;
use App\Http\Controllers\Api\FilterController;
use App\Http\Controllers\Api\HealthCheckController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\NoticeController;
use App\Http\Controllers\Api\OvertimeController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\PayrollRunController;
use App\Http\Controllers\Api\PeopleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RequestsController;
use App\Http\Controllers\Api\SfmController;
use App\Http\Controllers\Api\SndController;
use App\Http\Controllers\Api\TourPlanController;
use App\Http\Controllers\Api\TrainingController;
use App\Http\Middleware\EmployeeWriteAuthorization;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HRM Platform API Routes - Matching Postman Collection & Extensions
|--------------------------------------------------------------------------
*/

$registerHrmRoutes = function () {
    // Health & System
    Route::get('/health', HealthCheckController::class)->name('health');

    // Auth & User Profile & Email Verification
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('auth.login');

    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('auth.register');
    Route::post('/auth/send-verification-code', [AuthController::class, 'sendVerificationCode'])->middleware('throttle:5,1')->name('auth.send_code');
    Route::post('/auth/verify-code', [AuthController::class, 'verifyCode'])->middleware('throttle:5,1')->name('auth.verify_code');
    Route::middleware(['auth:sanctum', \App\Http\Middleware\EmployeeContext::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('auth.change_password');
    Route::get('/user', [AuthController::class, 'user'])->name('auth.user');

    // Dashboard
    Route::match(['get', 'post'], '/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Employee Management
    Route::prefix('hrm')->group(function () {
        Route::get('/profile', [EmployeeController::class, 'profile'])->name('employee.profile');
        Route::post('/profile-update', [EmployeeController::class, 'updateProfile'])->name('employee.profile.update');
        Route::get('/get-employees', [EmployeeController::class, 'getEmployees'])->name('employee.list');
        Route::get('/employees', [EmployeeController::class, 'getEmployees'])->name('employee.filter_list');
        Route::post('/employee-create', [EmployeeController::class, 'store'])->middleware(['auth:sanctum', EmployeeWriteAuthorization::class.':action.employees.create'])->name('employee.store');
        Route::post('/employees', [EmployeeController::class, 'store'])->middleware(['auth:sanctum', EmployeeWriteAuthorization::class.':action.employees.create'])->name('employee.store_alt');
        Route::match(['put', 'patch', 'post'], '/employee-update/{id}', [EmployeeController::class, 'update'])->middleware(['auth:sanctum', EmployeeWriteAuthorization::class.':action.employees.edit'])->name('employee.update');
        Route::match(['put', 'patch'], '/employees/{id}', [EmployeeController::class, 'update'])->middleware(['auth:sanctum', EmployeeWriteAuthorization::class.':action.employees.edit'])->name('employee.update_alt');
        Route::match(['delete', 'post'], '/employee-delete/{id}', [EmployeeController::class, 'destroy'])->middleware(['auth:sanctum', EmployeeWriteAuthorization::class.':action.employees.delete'])->name('employee.destroy');
        Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->middleware(['auth:sanctum', EmployeeWriteAuthorization::class.':action.employees.delete'])->name('employee.destroy_alt');
    });
    Route::get('/company-wise-users', [EmployeeController::class, 'companyWiseUsers'])->name('employee.company_users');

    // Role-Based Permissions & Access Control API
    Route::prefix('hrm')->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::get('/permissions/check', [PermissionController::class, 'check'])->name('permissions.check');
        Route::get('/permissions/employee/{id}', [PermissionController::class, 'getEmployeePermissions'])->name('permissions.employee');
        Route::match(['post', 'put'], '/permissions/employee/{id}', [PermissionController::class, 'updateEmployeePermissions'])->middleware(['auth:sanctum', EmployeeWriteAuthorization::class.':action.permissions.manage'])->name('permissions.employee_update');
        Route::post('/permissions/employee/{id}/grant-all', [PermissionController::class, 'grantAll'])->middleware(['auth:sanctum', EmployeeWriteAuthorization::class.':action.permissions.manage'])->name('permissions.grant_all');
        Route::post('/permissions/employee/{id}/revoke-all', [PermissionController::class, 'revokeAll'])->middleware(['auth:sanctum', EmployeeWriteAuthorization::class.':action.permissions.manage'])->name('permissions.revoke_all');
        Route::post('/permissions/employee/{id}/reset-defaults', [PermissionController::class, 'resetDefaults'])->middleware(['auth:sanctum', EmployeeWriteAuthorization::class.':action.permissions.manage'])->name('permissions.reset_defaults');
    });

    // Attendance & Biometrics
    Route::prefix('hrm')->group(function () {
        Route::match(['post', 'put', 'options'], '/mobile-attendance-store', [AttendanceController::class, 'mobileStore'])->name('attendance.mobile_store');
        Route::match(['post', 'put', 'options'], '/punch-in', [AttendanceController::class, 'punchIn'])->name('attendance.punch_in');
        Route::match(['post', 'put', 'options'], '/punch-out', [AttendanceController::class, 'punchOut'])->name('attendance.punch_out');
        Route::get('/today-attendance-report', [AttendanceController::class, 'todayReport'])->name('attendance.today_report');
        Route::get('/all-employees-today-attendance', [AttendanceController::class, 'allEmployeesTodayAttendance'])->name('attendance.all_today');
        Route::get('/employee-today-attendance', [AttendanceController::class, 'todayReport'])->name('attendance.emp_today');
        Route::get('/check-today-attendance', [AttendanceController::class, 'checkToday'])->name('attendance.check_today');
        Route::get('/v2/monthly-attendance-reports', [AttendanceController::class, 'monthlyReport'])->name('attendance.monthly_report');
        Route::get('/employee-job-card', [AttendanceController::class, 'jobCard'])->name('attendance.job_card');
        Route::get('/employee-current-shift', [AttendanceController::class, 'currentShift'])->name('attendance.shift');
    });
    Route::post('/push', [AttendanceController::class, 'push'])->name('attendance.push');
    Route::post('/push/v2', [AttendanceController::class, 'push'])->name('attendance.push.v2');
    Route::post('/push/v3', [AttendanceController::class, 'push'])->name('attendance.push.v3');

    // Leave Management
    Route::prefix('hrm')->group(function () {
        Route::get('/leave/types', [LeaveController::class, 'types'])->name('leave.types');
        Route::post('/leave/apply', [LeaveController::class, 'apply'])->name('leave.apply');
        Route::get('/leave-applications', [LeaveController::class, 'list'])->name('leave.list');
        Route::get('/pending-leave-applications', [LeaveController::class, 'pendingList'])->name('leave.pending');
        Route::get('/emp-leave-app-list', [LeaveController::class, 'list'])->name('leave.emp_list');
        Route::post('/recommend-leave-application/{id}', [LeaveController::class, 'recommend'])->name('leave.recommend');
        Route::post('/approve-leave-application/{id}', [LeaveController::class, 'approve'])->name('leave.approve');
        Route::post('/cancel-leave-application/{id}', [LeaveController::class, 'cancel'])->name('leave.cancel');
        Route::get('/leave/stats', [LeaveController::class, 'stats'])->name('leave.stats');
    });

    // Short Leave, Late Request & Shifts
    Route::prefix('hrm')->group(function () {
        Route::get('/short-leave/types', [RequestsController::class, 'shortLeaveTypes'])->name('short_leave.types');
        Route::post('/short-leave/apply', [RequestsController::class, 'applyShortLeave'])->name('short_leave.apply');
        Route::get('/short-leave/list', [RequestsController::class, 'listShortLeave'])->name('short_leave.list');
        Route::get('/iom-types', [RequestsController::class, 'iomTypes'])->name('late.iom_types');
        Route::get('/late-request', [RequestsController::class, 'listLateRequests'])->name('late.list');
        Route::post('/late-request/store', [RequestsController::class, 'storeLateRequest'])->name('late.store');
        Route::get('/get-shifts', [RequestsController::class, 'shiftApplications'])->name('shifts.list');
        Route::get('/shift-applications', [RequestsController::class, 'shiftApplications'])->name('shifts.applications');
        Route::post('/outwork/apply', [RequestsController::class, 'storeOutwork'])->name('outwork.apply');
        Route::get('/outwork/list', [RequestsController::class, 'listOutwork'])->name('outwork.list');
    });

    Route::prefix('hrm/factory')->middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::get('/operations', [FactoryController::class, 'index']);
        Route::post('/records', [FactoryController::class, 'store']);
    });

    Route::prefix('hrm/people')->middleware('throttle:60,1')->group(function () {
        Route::get('/overview', [PeopleController::class, 'overview']);
        Route::get('/records', [PeopleController::class, 'index']);
        Route::post('/records', [PeopleController::class, 'store']);
        Route::get('/records/{id}', [PeopleController::class, 'show']);
        Route::post('/records/{id}/files', [PeopleController::class, 'attach']);
        Route::get('/records/{id}/files/{file}', [PeopleController::class, 'download']);
    });

    // Payroll batches require authenticated, company-scoped permission checks.
    Route::prefix('hrm/payroll')->middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::get('/history', [PayrollRunController::class, 'history']);
        Route::get('/runs', [PayrollRunController::class, 'index']);
        Route::post('/preview', [PayrollRunController::class, 'preview']);
        Route::get('/runs/{id}', [PayrollRunController::class, 'show']);
        Route::post('/runs', [PayrollRunController::class, 'store']);
        Route::delete('/runs/{id}', [\App\Http\Controllers\Api\PayrollRunController::class, 'discard']);
        Route::post('/runs/{id}/action', [PayrollRunController::class, 'action']);
        Route::post('/runs/{id}/attachments', [PayrollRunController::class, 'attach']);
        Route::get('/runs/{id}/attachments/{attachment}', [PayrollRunController::class, 'download']);
    });

    // Salary & Payroll (Comprehensive HRM Extension)
    Route::prefix('hrm')->group(function () {
        Route::get('/payslip', [PayrollController::class, 'payslip'])->name('payroll.payslip');
        Route::get('/payslip/v2', [PayrollController::class, 'payslipV2'])->name('payroll.payslip.v2');
        Route::get('/payslips', [PayrollController::class, 'allPayslips'])->name('payroll.all_payslips');
        Route::get('/salary/structure', [PayrollController::class, 'salaryStructure'])->name('payroll.structure');

    });

    // Overtime Management & Rates (Admin, HR & High Officials)
    Route::prefix('hrm')->group(function () {
        Route::get('/overtime/rates', [OvertimeController::class, 'index'])->middleware(['auth:sanctum', \App\Http\Middleware\EmployeeWriteAuthorization::class.':module.salary'])->name('overtime.rates');
        Route::post('/overtime/set-rate', [OvertimeController::class, 'setRate'])->middleware(['auth:sanctum', \App\Http\Middleware\EmployeeWriteAuthorization::class.':action.employees.edit'])->name('overtime.set_rate');
        Route::post('/overtime/bulk-set', [OvertimeController::class, 'bulkSetRates'])->middleware(['auth:sanctum', \App\Http\Middleware\EmployeeWriteAuthorization::class.':action.employees.edit'])->name('overtime.bulk_set');
        Route::get('/overtime/logs', [OvertimeController::class, 'getLogs'])->middleware(['auth:sanctum', \App\Http\Middleware\EmployeeWriteAuthorization::class.':module.salary'])->name('overtime.logs');
    });

    // HR Loans
    Route::prefix('hrm')->group(function () {
        Route::post('/hr-loan-apply', [LoanController::class, 'apply'])->name('loan.apply');
        Route::get('/hr-loan-list', [LoanController::class, 'list'])->name('loan.list');
        Route::get('/hr-loan-status/{id}', [LoanController::class, 'status'])->name('loan.status');
    });

    // Tour Plans, Claims & Daily Work
    Route::prefix('hrm')->group(function () {
        Route::get('/get-work-types', [TourPlanController::class, 'workTypes'])->name('tour.work_types');
        Route::get('/get-transport-types', [TourPlanController::class, 'transportTypes'])->name('tour.transport_types');
        Route::get('/tour-plans', [TourPlanController::class, 'tourPlans'])->name('tour.list');
        Route::get('/tour-plans/detail', [TourPlanController::class, 'tourPlanDetail'])->name('tour.detail');
        Route::post('/tour-plans/store', [TourPlanController::class, 'storeTourPlan'])->name('tour.store');
        Route::put('/tour-plans/{id}/update', [TourPlanController::class, 'updateTourPlan'])->name('tour.update');
        Route::put('/approved-tour-plans', [TourPlanController::class, 'approveTourPlans'])->name('tour.approve');
        Route::post('/punch-plan', [TourPlanController::class, 'punchPlan'])->name('tour.punch');

        Route::get('/tour-plans-claims', [TourPlanController::class, 'tourPlansClaims'])->name('claims.tour_list');
        Route::get('/tour-plans-claims/{id}', [TourPlanController::class, 'tourPlanClaimDetail'])->name('claims.tour_detail');
        Route::get('/tour-plans-claims/da/{id}', [TourPlanController::class, 'tourPlanClaimDa'])->name('claims.tour_da');
        Route::get('/claims-list', [TourPlanController::class, 'claimsList'])->name('claims.list');
        Route::get('/claims-list/{id}', [TourPlanController::class, 'claimDetail'])->name('claims.detail');
        Route::post('/tour-plans-claims/store', [TourPlanController::class, 'storeTourPlanClaim'])->name('claims.store');
        Route::get('/get-expense-types', [TourPlanController::class, 'expenseTypes'])->name('claims.expense_types');
        Route::post('/other-expenses/store', [TourPlanController::class, 'storeOtherExpense'])->name('claims.other_expense');

        Route::get('/daily-work', [TourPlanController::class, 'dailyWorkList'])->name('daily_work.list');
        Route::post('/daily-work/apply', [TourPlanController::class, 'applyDailyWork'])->name('daily_work.apply');
    });

    // Notices
    Route::prefix('hrm')->group(function () {
        Route::get('/notice-list', [NoticeController::class, 'list'])->name('notice.list');
        Route::post('/notice-create', [NoticeController::class, 'store'])->name('notice.create');
        Route::get('/notice/show', [NoticeController::class, 'show'])->name('notice.show');
    });

    // Filters & Metadata Lookups
    Route::prefix('hrm')->group(function () {
        Route::get('/companies', [FilterController::class, 'companies'])->name('filter.companies');
        Route::get('/departments', [FilterController::class, 'departments'])->name('filter.departments');
        Route::get('/designations', [FilterController::class, 'designations'])->name('filter.designations');
        Route::get('/employee-groups', [FilterController::class, 'employeeGroups'])->name('filter.groups');

        // Trainings
        Route::get('/tranings', [TrainingController::class, 'index'])->name('trainings.list');
        Route::post('/traning/store', [TrainingController::class, 'store'])->name('trainings.store');
        Route::post('/emp-training-store', [TrainingController::class, 'enroll'])->name('trainings.enroll');
        Route::get('/get-emp-trainings', [TrainingController::class, 'index'])->name('trainings.emp_list');
    });

    // ==========================================
    // Accounting & Financial Management Endpoints
    // ==========================================
    Route::prefix('accounting')->group(function () {
        Route::get('/summary', [AccountingController::class, 'summary'])->name('accounting.summary');
        Route::get('/vouchers', [AccountingController::class, 'vouchers'])->name('accounting.vouchers');
        Route::post('/vouchers/store', [AccountingController::class, 'storeVoucher'])->name('accounting.vouchers.store');
        Route::get('/chart-of-accounts', [AccountingController::class, 'chartOfAccounts'])->name('accounting.coa');
        Route::get('/payroll-reconciliations', [AccountingController::class, 'payrollReconciliations'])->name('accounting.payroll_reconciliations');
        Route::get('/petty-cash', [AccountingController::class, 'pettyCash'])->name('accounting.petty_cash');
        Route::get('/financial-statements', [AccountingController::class, 'financialStatements'])->name('accounting.statements');
    });

    // ==========================================
    // SND (Sales & Distribution) Endpoints
    // ==========================================
    Route::prefix('snd')->group(function () {
        Route::get('/dashboard', [SndController::class, 'dashboard'])->name('snd.dashboard');

        // Setups
        Route::get('/setups/customer-types', [SndController::class, 'customerTypes'])->name('snd.customer_types');
        Route::get('/setups/owner-types', [SndController::class, 'ownerTypes'])->name('snd.owner_types');
        Route::get('/setups/depots', [SndController::class, 'depots'])->name('snd.depots');
        Route::get('/setups/not-order-reasons', [SndController::class, 'notOrderReasons'])->name('snd.not_order_reasons');

        // Customers
        Route::get('/customers', [SndController::class, 'customers'])->name('snd.customers');
        Route::post('/customers/store', [SndController::class, 'storeCustomer'])->name('snd.customers.store');
        Route::get('/thana-wise-customers', [SndController::class, 'thanaWiseCustomers'])->name('snd.thana_customers');
        Route::get('/check-thana-depots', [SndController::class, 'checkThanaDepots'])->name('snd.check_thana');
        Route::get('/get-depot-by-customer/{customer_id}', [SndController::class, 'getDepotByCustomer'])->name('snd.depot_by_customer');

        // Sales Orders & Punching
        Route::post('/sales/punch', [SndController::class, 'punchSales'])->name('snd.punch_sales');
        Route::get('/sales-orders', [SndController::class, 'salesOrders'])->name('snd.sales_orders');
        Route::post('/sales-orders/store', [SndController::class, 'storeSalesOrder'])->name('snd.sales_orders.store');
        Route::get('/sales-orders/products', [SndController::class, 'products'])->name('snd.products');
        Route::post('/sales-orders/{id}/approve', [SndController::class, 'approveSalesOrder'])->name('snd.sales_orders.approve');
        Route::post('/sales-orders/{id}/reject', [SndController::class, 'rejectSalesOrder'])->name('snd.sales_orders.reject');
        Route::put('/sales-orders/{id}/update', [SndController::class, 'updateSalesOrder'])->name('snd.sales_orders.update');

        // Sales Representatives
        Route::get('/sales-representatives', [SndController::class, 'salesRepresentatives'])->name('snd.sales_reps');

        // SR Reports & Monitoring
        Route::get('/sr-outlet-visit-report', [SndController::class, 'outletVisitReport'])->name('snd.outlet_visit');
        Route::get('/sr-outlet-visit-report/filters', [SndController::class, 'outletVisitReportFilters'])->name('snd.outlet_visit.filters');
        Route::get('/sr-outlet-visit-report/export', [SndController::class, 'outletVisitReportExport'])->name('snd.outlet_visit.export');

        Route::get('/sr-market-order-report', [SndController::class, 'marketOrderReport'])->name('snd.market_order');
        Route::get('/sr-market-order-report/filters', [SndController::class, 'marketOrderReportFilters'])->name('snd.market_order.filters');
        Route::get('/sr-market-order-report/export', [SndController::class, 'marketOrderReportExport'])->name('snd.market_order.export');

        Route::get('/sr-monitoring-dashboard', [SndController::class, 'monitoringDashboard'])->name('snd.monitoring');
        Route::get('/sr-monitoring-dashboard/filters', [SndController::class, 'monitoringDashboardFilters'])->name('snd.monitoring.filters');
        Route::get('/sr-monitoring-dashboard/export', [SndController::class, 'monitoringDashboardExport'])->name('snd.monitoring.export');
    });

    // ==========================================
    // SFM (Sales Force Management) Endpoints
    // ==========================================
    Route::prefix('sfm')->group(function () {
        // Geographic Setups
        Route::get('/setups/countries', [SfmController::class, 'countries'])->name('sfm.countries');
        Route::get('/setups/divisions', [SfmController::class, 'divisions'])->name('sfm.divisions');
        Route::get('/setups/nationals', [SfmController::class, 'nationals'])->name('sfm.nationals');
        Route::get('/setups/regions', [SfmController::class, 'regions'])->name('sfm.regions');
        Route::get('/setups/zones', [SfmController::class, 'zones'])->name('sfm.zones');
        Route::get('/setups/bases', [SfmController::class, 'bases'])->name('sfm.bases');
        Route::get('/setups/thanas', [SfmController::class, 'thanas'])->name('sfm.thanas');
        Route::get('/setups/unions', [SfmController::class, 'unions'])->name('sfm.unions');

        // Target Commitments
        Route::get('/target-dashboard', [SfmController::class, 'targetDashboard'])->name('sfm.target_dashboard');
        Route::get('/target-commitments', [SfmController::class, 'targetCommitments'])->name('sfm.target_commitments');
        Route::get('/target-commitments/user-assigned-target', [SfmController::class, 'userAssignedTarget'])->name('sfm.user_assigned_target');
        Route::put('/target-commitments/{id}/update', [SfmController::class, 'updateCommitment'])->name('sfm.target_commitments.update');

        // Reports & Field Audits
        Route::get('/reports/shop-visiting-report', [SfmController::class, 'shopVisitingReport'])->name('sfm.shop_visiting');
        Route::get('/reports/shop-visiting-report/filters', [SfmController::class, 'shopVisitingReportFilters'])->name('sfm.shop_visiting.filters');
        Route::get('/reports/shop-visiting-report/shop-summary', [SfmController::class, 'shopSummary'])->name('sfm.shop_summary');
        Route::get('/reports/shop-visiting-report/sr-summary', [SfmController::class, 'srSummary'])->name('sfm.sr_summary');
        Route::get('/reports/shop-visiting-report/reasons-breakdown', [SfmController::class, 'reasonsBreakdown'])->name('sfm.reasons_breakdown');

        Route::get('/reports/attendance-report/filters', [SfmController::class, 'attendanceReportFilters'])->name('sfm.attendance_report.filters');
        Route::get('/reports/date-wise-attendance-report', [SfmController::class, 'dateWiseAttendanceReport'])->name('sfm.date_wise_attendance');
        Route::get('/reports/employee-job-card', [SfmController::class, 'employeeJobCard'])->name('sfm.employee_job_card');
        Route::get('/reports/employee-job-card/multi-pdf', [SfmController::class, 'employeeJobCardMultiPdf'])->name('sfm.job_card_pdf');

        // SFM Employees
        Route::get('/employees', [SfmController::class, 'sfmEmployees'])->name('sfm.employees');
    });
    });
};

// Register for /api/v1/*
Route::prefix('v1')->name('v1.')->group($registerHrmRoutes);

// Register for /api/* directly (matching raw Postman endpoints)
$registerHrmRoutes();
