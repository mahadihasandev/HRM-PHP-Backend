<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTO\PeopleRecordDTO;
use App\Http\Requests\PeopleFileRequest;
use App\Http\Requests\PeopleReadRequest;
use App\Http\Requests\PeopleStoreRequest;
use App\Http\Resources\PeopleRecordResource;
use App\Services\PeopleService;

class PeopleController extends BaseApiController
{
    public function __construct(private PeopleService $service) {}

    public function index(PeopleReadRequest $request)
    {
        $page = $this->service->listing($this->service->actor($request->user()), $request->validated())['page'];

        return $this->successResponse(['records' => PeopleRecordResource::collection($page->items()),
            'pagination' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()]]);
    }

    public function overview(PeopleReadRequest $request)
    {
        return $this->successResponse($this->service->overview($this->service->actor($request->user())));
    }

    public function show(PeopleReadRequest $request, int $id)
    {
        return $this->successResponse(new PeopleRecordResource($this->service->record($this->service->actor($request->user()), $id)));
    }

    public function store(PeopleStoreRequest $request)
    {
        return $this->successResponse(new PeopleRecordResource($this->service->save($this->service->authorizeWrite($request->user(), $request->input('kind')), $request->user(), PeopleRecordDTO::fromRequest($request)->toArray())));
    }

    public function attach(PeopleFileRequest $request, int $id)
    {
        return $this->successResponse(new PeopleRecordResource($this->service->attach($this->service->actor($request->user()), $request->user(), $id, $request->file('file'))));
    }

    public function download(PeopleReadRequest $request, int $id, int $file)
    {
        return $this->service->download($this->service->actor($request->user()), $id, $file);
    }
}
