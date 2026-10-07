<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Repositories\Contracts\PayrollRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeWriteAuthorization
{
    public function __construct(private PayrollRepositoryInterface $repository) {}

    public function handle(Request $request, Closure $next, string $permission)
    {
        $actor = $this->repository->actor($request->user());
        abort_unless($actor && $actor->status === 'Active' && $this->repository->allowed($actor, $permission), 403, 'Employee management permission is required.');
        $id = $request->route('id');
        if ($id !== null) {
            $query = DB::table('employees')->where('company_id', $actor->company_id)->where(function ($query) use ($id) {
                $query->where('employee_full_id', (string) $id)->orWhere('email', (string) $id);
                if (is_numeric($id)) {
                    $query->orWhere('id', (int) $id)->orWhere('employee_id', (int) $id);
                }
            });
            abort_unless($query->exists(), 404, 'Employee not found in your company.');
        }
        $request->attributes->set('employee_actor', $actor);

        return $next($request);
    }
}
