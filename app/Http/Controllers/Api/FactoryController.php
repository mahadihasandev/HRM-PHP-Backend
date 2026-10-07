<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTO\FactoryRecordDTO;
use App\Http\Requests\FactoryReadRequest;
use App\Http\Requests\FactoryStoreRequest;
use App\Http\Resources\FactoryRecordResource;
use App\Services\FactoryService;

class FactoryController extends BaseApiController
{
    public function __construct(private FactoryService $service) {}

    public function index(FactoryReadRequest $request)
    {
        $data = $this->service->dashboard($this->service->actor($request->user()));

        return $this->successResponse(array_map(fn ($records) => FactoryRecordResource::collection($records), $data));
    }

    public function store(FactoryStoreRequest $request)
    {
        return $this->successResponse(new FactoryRecordResource($this->service->save($this->service->actor($request->user(), true), $request->user(), FactoryRecordDTO::fromRequest($request)->toArray())));
    }
}
