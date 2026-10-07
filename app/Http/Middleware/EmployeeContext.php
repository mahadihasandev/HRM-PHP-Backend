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
        $controller = class_basename($request->route()->getControllerClass());
        // These inherited demo modules lack persistence or company isolation.
        $legacy = ['AccountingController', 'LoanController', 'NoticeController', 'RequestsController', 'SfmController', 'SndController', 'TourPlanController', 'TrainingController', 'FilterController'];
        abort_if(app()->isProduction() && in_array($controller, $legacy, true), 503, 'This legacy module requires company-scoped implementation before production use.');

        return $next($request);
    }
}
