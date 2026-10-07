<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\ApplyLeaveRequest;
use App\Http\Requests\TransitionLeaveRequest;
use App\Services\Employees\LeaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveController extends BaseApiController
{
    public function __construct(private LeaveService $service) {}

    public function types(Request $request): JsonResponse
    {
        return $this->successResponse([['id' => 1, 'name' => 'Casual Leave'], ['id' => 2, 'name' => 'Earned Leave'], ['id' => 3, 'name' => 'Sick Leave'], ['id' => 4, 'name' => 'Maternity Leave']], 'Leave categories; allowances require company policy configuration');
    }

    public function apply(ApplyLeaveRequest $request): JsonResponse
    {
        return $this->successResponse($this->service->apply($request), 'Leave application submitted successfully');
    }

    public function list(Request $request): JsonResponse
    {
        return $this->successResponse($this->service->query($request)->orderByDesc('id')->get());
    }

    public function pendingList(Request $request): JsonResponse
    {
        return $this->successResponse($this->service->query($request)->where('status', 'like', 'Pending%')->orderByDesc('id')->get());
    }

    public function recommend(TransitionLeaveRequest $request, int $id): JsonResponse
    {
        return $this->successResponse($this->service->transition($request, $id, 'recommend'), 'Leave recommended successfully');
    }

    public function approve(TransitionLeaveRequest $request, int $id): JsonResponse
    {
        return $this->successResponse($this->service->transition($request, $id, 'approve'), 'Leave approved successfully');
    }

    public function cancel(TransitionLeaveRequest $request, int $id): JsonResponse
    {
        return $this->successResponse($this->service->transition($request, $id, 'cancel'), 'Leave cancelled successfully');
    }

    public function stats(Request $request): JsonResponse
    {
        return $this->successResponse(['approved_days' => (int) $this->service->query($request)->where('status', 'Approved')->sum('days_count'), 'balances_configured' => false], 'Company leave allowances are not configured');
    }
}
