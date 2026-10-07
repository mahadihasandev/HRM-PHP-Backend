<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeopleRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'kind' => $this->kind, 'employee_full_id' => $this->employee_full_id,
            'title' => $this->title, 'start_date' => $this->start_date, 'due_date' => $this->due_date,
            'status' => $this->status, 'details' => $this->details, 'version' => $this->version,
            'updated_at' => $this->updated_at->toIso8601String(),
            'files' => $this->whenLoaded('files', fn () => $this->files->map(fn ($file) => [
                'id' => $file->id, 'name' => $file->name, 'size' => $file->size,
            ])),
            'events' => $this->whenLoaded('events', fn () => $this->events->map(fn ($event) => [
                'id' => $event->id, 'actor_name' => $event->actor_name, 'action' => $event->action,
                'status' => $event->status, 'version' => $event->version, 'created_at' => $event->created_at->toIso8601String(),
            ])),
        ];
    }
}
