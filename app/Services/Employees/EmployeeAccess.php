<?php

declare(strict_types=1);

namespace App\Services\Employees;

use App\Repositories\Contracts\PayrollRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeAccess extends BaseService
{
    public function __construct(private PayrollRepositoryInterface $repository) {}

    public function actor(Request $request): object
    {
        abort_unless($request->user(), 401, 'Authentication is required.');
        $actor = $this->repository->actor($request->user());
        abort_unless($actor && $actor->status === 'Active', 403, 'An active employee account is required.');

        return $actor;
    }

    public function allowed(object $actor, string $permission): bool
    {
        return $this->repository->allowed($actor, $permission);
    }

    public function target(Request $request, string $permission): object
    {
        $actor = $this->actor($request);
        $id = $request->input('employee_full_id') ?? $request->header('X-Employee-Id') ?? $request->header('X-Operator-Id');
        if (! $id || (string) $id === $actor->employee_full_id || (string) $id === (string) $actor->id) {
            return $actor;
        }
        abort_unless($request->isMethod('GET') && $this->allowed($actor, $permission), 403, 'Access to another employee is not granted.');

        return DB::table('employees')->where('company_id', $actor->company_id)
            ->where(function ($query) use ($id) {
                $query->where('employee_full_id', (string) $id);
                if (is_numeric($id)) {
                    $query->orWhere('id', (int) $id);
                }
            })->firstOrFail();
    }
}
