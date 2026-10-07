<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\Employees\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends BaseApiController
{
    public function __construct(private DashboardService $service) {}

    public function index(Request $request): JsonResponse
    {
        return $this->successResponse($this->service->metrics($request), 'Dashboard metrics retrieved successfully');
    }
}
