<?php

declare(strict_types=1);

namespace App\Services\Employees;

use App\Services\BaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollStatementService extends BaseService
{
    public function __construct(private EmployeeAccess $access) {}

    public function payslip(Request $request): mixed
    {
        $employee = $this->access->target($request, 'module.salary');
        $slip = DB::table('payslips')->where('employee_full_id', $employee->employee_full_id)->where('month', $request->input('month', now()->format('Y-m')))->first();
        abort_unless($slip, 404, 'No stored payslip exists for this month.');

        return $slip;
    }

    public function payslipV2(Request $request): mixed
    {
        return $this->payslip($request);
    }

    public function salaryStructure(Request $request): mixed
    {
        $emp = $this->access->target($request, 'module.salary');

        return ['employee_full_id' => $emp->employee_full_id, 'employee_name' => $emp->name, 'gross_salary' => (float) $emp->gross_salary, 'net_payable' => (float) $emp->net_payable, 'overtime_rate' => (float) $emp->overtime_rate,
            'components' => [['name' => 'Basic salary', 'type' => 'Earning', 'amount' => (float) $emp->basic_salary], ['name' => 'House rent', 'type' => 'Earning', 'amount' => (float) $emp->house_rent], ['name' => 'Medical allowance', 'type' => 'Earning', 'amount' => (float) $emp->medical_allowance], ['name' => 'Conveyance', 'type' => 'Earning', 'amount' => (float) $emp->conveyance], ['name' => 'Provident fund', 'type' => 'Deduction', 'amount' => (float) $emp->pf_deduction], ['name' => 'Tax deduction', 'type' => 'Deduction', 'amount' => (float) $emp->tax_deduction]]];
    }

    public function allPayslips(Request $request): mixed
    {
        $actor = $this->access->actor($request);
        abort_unless($this->access->allowed($actor, 'module.salary'), 403, 'Payroll access is not granted.');

        return DB::table('payslips')->whereIn('employee_full_id', DB::table('employees')->where('company_id', $actor->company_id)->select('employee_full_id'))->orderByDesc('id')->get();
    }
}
