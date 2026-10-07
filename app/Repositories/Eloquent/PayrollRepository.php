<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Http\Controllers\Api\PermissionController;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\User;
use App\Repositories\Contracts\PayrollRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayrollRepository implements PayrollRepositoryInterface
{
    public function actor(User $user): ?object
    {
        return DB::table('employees')->where('email', $user->email)
            ->orWhere('employee_full_id', str_replace('@smarterp.biz', '', $user->email))->first();
    }

    public function allowed(object $actor, string $permission): bool
    {
        $override = DB::table('employee_permissions')->where('employee_full_id', $actor->employee_full_id)
            ->where('permission_key', $permission)->value('is_granted');

        return $override !== null ? (bool) $override : (PermissionController::$departmentProfiles[$actor->department][$permission] ?? ($permission === 'module.people'));
    }

    public function employees(int $companyId): Collection
    {
        return DB::table('employees')->where('company_id', $companyId)->get()->keyBy('employee_full_id');
    }

    public function existingEmployees(int $companyId, string $month): array
    {
        $current = PayrollItem::where('company_id', $companyId)->where('month', $month)->pluck('employee_full_id')->all();
        $legacy = DB::table('payslips')->join('employees', 'employees.employee_full_id', '=', 'payslips.employee_full_id')
            ->where('employees.company_id', $companyId)->where('payslips.month', $month)
            ->whereIn('payslips.status', ['Paid', 'Approved', 'paid', 'approved'])->pluck('payslips.employee_full_id')->all();

        return array_values(array_unique(array_merge($current, $legacy)));
    }

    public function history(int $companyId): Collection
    {
        return DB::table('payslips')->join('employees', 'employees.employee_full_id', '=', 'payslips.employee_full_id')
            ->where('employees.company_id', $companyId)->select('payslips.*')->orderByDesc('payslips.id')->limit(1000)->get();
    }

    public function runs(int $companyId): Collection
    {
        return PayrollRun::where('company_id', $companyId)->withCount('items')
            ->withSum('items', 'total_earnings')->withSum('items', 'total_deductions')
            ->withSum('items', 'net_payable')->withSum('items', 'overtime_amount')
            ->withSum(['items as bank_total' => fn ($query) => $query->where('payment_method', 'bank')], 'net_payable')
            ->withSum(['items as cash_total' => fn ($query) => $query->where('payment_method', 'cash')], 'net_payable')
            ->latest()->limit(100)->get();
    }

    public function run(int $companyId, int $id, bool $lock = false): PayrollRun
    {
        $query = PayrollRun::where('company_id', $companyId)->whereKey($id);
        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->with('items', 'attachments')->firstOrFail();
    }

    public function create(array $attributes, array $rows): PayrollRun
    {
        $run = PayrollRun::create($attributes);
        $run->items()->createMany($rows);

        return $run->load('items', 'attachments');
    }
}
