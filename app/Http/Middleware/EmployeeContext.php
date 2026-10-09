<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Employees\EmployeeAccess;
use Closure;
use Illuminate\Http\Request;

class EmployeeContext
{
    public function __construct(private EmployeeAccess $access) {}

    public function handle(Request $request, Closure $next)
    {
        $request->attributes->set('employee_actor', $this->access->actor($request));

        return $next($request);
    }
}
