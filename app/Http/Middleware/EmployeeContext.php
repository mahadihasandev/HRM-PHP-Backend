<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Controllers\Api\AccountingController;
use App\Http\Controllers\Api\FilterController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\NoticeController;
use App\Http\Controllers\Api\RequestsController;
use App\Http\Controllers\Api\SfmController;
use App\Http\Controllers\Api\SndController;
use App\Http\Controllers\Api\TourPlanController;
use App\Http\Controllers\Api\TrainingController;
use App\Services\Employees\EmployeeAccess;
use Closure;
use Illuminate\Http\Request;

class EmployeeContext
{
    // Keep demo/global-data controllers unavailable until company scoping and
    // authenticated ownership are implemented and covered by regression tests.
    private const UNSCOPED_CONTROLLERS = [
        AccountingController::class,
        LoanController::class,
        NoticeController::class,
        RequestsController::class,
        SfmController::class,
        SndController::class,
        TourPlanController::class,
        TrainingController::class,
        FilterController::class,
    ];

    public function __construct(private EmployeeAccess $access) {}

    public function handle(Request $request, Closure $next)
    {
        $request->attributes->set('employee_actor', $this->access->actor($request));

        abort_if(
            app()->isProduction()
                && in_array($request->route()?->getControllerClass(), self::UNSCOPED_CONTROLLERS, true),
            503,
            'This legacy module requires company-scoped implementation before production use.',
        );

        return $next($request);
    }
}
