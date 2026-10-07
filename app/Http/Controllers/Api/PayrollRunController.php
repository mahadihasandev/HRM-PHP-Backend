<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTO\PayrollImportDTO;
use App\Http\Requests\Payroll\PayrollActionRequest;
use App\Http\Requests\Payroll\PayrollAttachmentRequest;
use App\Http\Requests\Payroll\PayrollImportRequest;
use App\Http\Requests\Payroll\PayrollReadRequest;
use App\Http\Requests\Payroll\PayrollWriteRequest;
use App\Http\Resources\LegacyPayslipResource;
use App\Http\Resources\PayrollRunResource;
use App\Repositories\Contracts\PayrollRepositoryInterface;
use App\Services\Payroll\PayrollAccess;
use App\Services\Payroll\PayrollRunService;
use Illuminate\Support\Facades\Storage;

class PayrollRunController extends BaseApiController
{
    public function __construct(private PayrollRunService $service, private PayrollAccess $access, private PayrollRepositoryInterface $repository) {}

    public function index(PayrollReadRequest $request)
    {
        $actor = $this->access->actor($request->user());

        return $this->successResponse(PayrollRunResource::collection($this->repository->runs((int) $actor->company_id)));
    }

    public function history(PayrollReadRequest $request)
    {
        $actor = $this->access->actor($request->user());

        return $this->successResponse(LegacyPayslipResource::collection($this->repository->history((int) $actor->company_id)));
    }

    public function show(PayrollReadRequest $request, int $id)
    {
        $actor = $this->access->actor($request->user());

        return $this->successResponse(new PayrollRunResource($this->repository->run((int) $actor->company_id, $id)));
    }

    public function preview(PayrollImportRequest $request)
    {
        return $this->successResponse($this->service->preview(PayrollImportDTO::fromRequest($request), $this->access->actor($request->user(), true)));
    }

    public function store(PayrollImportRequest $request)
    {
        return $this->successResponse(new PayrollRunResource($this->service->create(PayrollImportDTO::fromRequest($request), $this->access->actor($request->user(), true), $request->user()->id)), 'Draft payroll saved.', 201);
    }

    public function action(PayrollActionRequest $request, int $id)
    {
        return $this->successResponse(new PayrollRunResource($this->service->transition($this->access->actor($request->user(), true), $id, $request->user()->id, $request->validated())));
    }

    public function attach(PayrollAttachmentRequest $request, int $id)
    {
        return $this->successResponse(new PayrollRunResource($this->service->attach($this->access->actor($request->user(), true), $id, $request->file('file'))));
    }

    public function discard(PayrollWriteRequest $request, int $id)
    {
        $this->service->discard($this->access->actor($request->user(), true), $id);

        return $this->successResponse(null, 'Draft discarded. You can import a corrected salary sheet.');
    }

    public function download(PayrollReadRequest $request, int $id, int $attachment)
    {
        $actor = $this->access->actor($request->user());
        $file = $this->repository->run((int) $actor->company_id, $id)->attachments->firstWhere('id', $attachment);
        abort_unless($file, 404);

        return Storage::disk('local')->download($file->path, $file->name, ['Cache-Control' => 'private, no-store']);
    }
}
