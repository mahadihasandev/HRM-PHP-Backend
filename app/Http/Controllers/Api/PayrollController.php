<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\PayrollMonthRequest;
use App\Services\Employees\PayrollStatementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollController extends BaseApiController
{
    public function __construct(private PayrollStatementService $service) {}

    public function payslip(PayrollMonthRequest $request): JsonResponse
    {
        return $this->successResponse($this->service->payslip($request));
    }

    public function payslipV2(PayrollMonthRequest $request): JsonResponse
    {
        return $this->payslip($request);
    }

    public function salaryStructure(Request $request): JsonResponse
    {
        return $this->successResponse($this->service->salaryStructure($request));
    }

    public function allPayslips(Request $request): JsonResponse
    {
        return $this->successResponse($this->service->allPayslips($request));
    }
}
