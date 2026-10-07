<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\HrRecord;
use App\Models\User;
use App\Repositories\Contracts\FactoryRepositoryInterface;
use App\Repositories\Contracts\PayrollRepositoryInterface;
use App\Repositories\Contracts\PeopleRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PeopleService extends BaseService
{
    public function __construct(
        private PeopleRepositoryInterface $repository,
        private PayrollRepositoryInterface $access,
        private FactoryRepositoryInterface $factory,
    ) {}

    public function actor(User $user): object
    {
        $actor = $this->access->actor($user);
        abort_unless($actor && $actor->status === 'Active' && $this->access->allowed($actor, 'module.people'), 403, 'HR workspace access is not granted.');

        return $actor;
    }

    public function capabilities(object $actor): array
    {
        return ['manage' => $this->access->allowed($actor, 'action.people.manage'),
            'grievances' => $this->access->allowed($actor, 'action.people.grievances')];
    }

    public function authorizeWrite(User $user, string $kind): object
    {
        $actor = $this->actor($user);
        if (! in_array($kind, ['grievance', 'incident'], true)) {
            abort_unless($this->capabilities($actor)['manage'], 403, 'HR management access is not granted.');
        }

        return $actor;
    }

    private function canManage(object $actor, string $kind): bool
    {
        $permissions = $this->capabilities($actor);

        return $kind === 'grievance' ? $permissions['grievances'] : $permissions['manage'];
    }

    private function visible(object $actor): Builder
    {
        $permissions = $this->capabilities($actor);

        return $this->repository->records((int) $actor->company_id)->where(function (Builder $query) use ($actor, $permissions) {
            $query->where('kind', 'holiday')->orWhere('employee_full_id', $actor->employee_full_id);
            if ($permissions['manage']) {
                $query->orWhereIn('kind', ['document', 'roster', 'training', 'incident']);
            }
            if ($permissions['grievances']) {
                $query->orWhere('kind', 'grievance');
            }
            $query->orWhere(function (Builder $training) use ($actor) {
                $training->where('kind', 'training')->whereHas('participants', fn ($participants) => $participants->where('employee_full_id', $actor->employee_full_id));
            });
        });
    }

    public function listing(object $actor, array $filters): array
    {
        $query = $this->visible($actor)->where('kind', $filters['kind'] ?? 'document');
        if (! empty($filters['q'])) {
            // Literal substring matching; database escaping differs across engines.
            $query->where(function (Builder $search) use ($filters) {
                $search->where('title', 'like', '%'.$filters['q'].'%')->orWhere('employee_full_id', 'like', '%'.$filters['q'].'%');
            });
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        $page = $query->with('files')->latest('id')->paginate(25);
        foreach ($page->items() as $record) {
            $this->redact($actor, $record);
        }

        return ['page' => $page];
    }

    public function overview(object $actor): array
    {
        $today = now('Asia/Dhaka')->toDateString();
        $soon = now('Asia/Dhaka')->addDays(30)->toDateString();
        $query = $this->visible($actor);

        return [
            'capabilities' => $this->capabilities($actor),
            'employee_full_id' => $actor->employee_full_id,
            'stats' => [
                'documents_due' => (clone $query)->where('kind', 'document')->whereIn('status', ['pending', 'verified'])->whereBetween('due_date', [$today, $soon])->count(),
                'overdue_actions' => (clone $query)->whereIn('kind', ['grievance', 'incident'])->whereIn('status', ['open', 'in_review', 'in_progress'])->where('due_date', '<', $today)->count(),
                'open_concerns' => (clone $query)->whereIn('kind', ['grievance', 'incident'])->whereIn('status', ['open', 'in_review', 'in_progress'])->count(),
                'upcoming_training' => (clone $query)->where('kind', 'training')->where('status', 'planned')->whereBetween('start_date', [$today, $soon])->count(),
            ],
            'employees' => $this->capabilities($actor)['manage'] ? $this->repository->employees((int) $actor->company_id)->values() : [],
            'setups' => $this->capabilities($actor)['manage'] ? $this->factory->list((int) $actor->company_id, 'setup')->filter(fn ($setup) => $setup->active)->values() : $this->factory->list((int) $actor->company_id, 'setup')->filter(fn ($setup) => $setup->active && $setup->kind === 'factory')->values(),
        ];
    }

    public function record(object $actor, int $id): HrRecord
    {
        return $this->redact($actor, $this->visible($actor)->whereKey($id)->with('files', 'events')->firstOrFail());
    }

    private function redact(object $actor, HrRecord $record): HrRecord
    {
        if ($record->kind === 'training' && ! $this->capabilities($actor)['manage']) {
            $details = $record->details;
            $details['participants'] = array_values(array_filter($details['participants'], fn ($participant) => $participant['employee_full_id'] === $actor->employee_full_id));
            $record->details = $details;
        }

        return $record;
    }

    public function save(object $actor, User $user, array $data): HrRecord
    {
        return DB::transaction(function () use ($actor, $user, $data) {
            $id = $data['id'] ?? null;
            $record = $id ? $this->repository->record((int) $actor->company_id, $id, true) : null;
            $kind = $data['kind'];
            $canManage = $this->canManage($actor, $kind);
            if ($record) {
                abort_unless($record->kind === $kind, 422, 'A record cannot change its category.');
                abort_unless($canManage, 403, 'Only an authorized handler may update this record.');
                abort_unless($record->version === (int) $data['version'], 409, 'This record changed. Reload it before saving.');
            } elseif (in_array($kind, ['grievance', 'incident'], true)) {
                // Reports always belong to the signed-in worker, including HR submissions.
                $data['employee_full_id'] = $actor->employee_full_id;
                if (! $canManage) {
                    $data['status'] = 'open';
                    if ($kind === 'grievance') {
                        $data['details']['resolution'] = null;
                    }
                    if ($kind === 'incident') {
                        $data['details']['corrective_action'] = null;
                        $data['details']['responsible_person'] = null;
                    }
                }
            }
            if ($record && in_array($kind, ['grievance', 'incident'], true)) {
                $data['employee_full_id'] = $record->employee_full_id;
            }
            if (in_array($kind, ['training', 'holiday'], true)) {
                $data['employee_full_id'] = null;
            }
            $company = (int) $actor->company_id;
            if (! empty($data['employee_full_id'])) {
                $employee = $this->repository->employee($company, $data['employee_full_id'], $kind === 'roster');
                if (! $employee || ((! $record || ($kind === 'roster' && $data['status'] === 'active')) && $employee->status !== 'Active')) {
                    throw ValidationException::withMessages(['employee_full_id' => 'Choose an active employee in your company.']);
                }
            }
            if ($kind === 'roster') {
                $this->requireSetup($company, 'factory', $data['details']['factory_code']);
                $this->requireSetup($company, 'shift', $data['details']['shift_code']);
                if (! empty($data['details']['line_code'])) {
                    $line = $this->requireSetup($company, 'line', $data['details']['line_code']);
                    if ($line->details['factory_code'] !== $data['details']['factory_code']) {
                        throw ValidationException::withMessages(['details.line_code' => 'The line must belong to the selected factory.']);
                    }
                }
                if ($data['status'] === 'active' && $this->repository->records($company)->where('kind', 'roster')
                    ->where('employee_full_id', $data['employee_full_id'])->where('status', 'active')
                    ->when($id, fn ($query) => $query->where('id', '!=', $id))
                    ->where('start_date', '<=', $data['due_date'])->where('due_date', '>=', $data['start_date'])->exists()) {
                    throw ValidationException::withMessages(['start_date' => 'This employee already has an assignment overlapping these dates.']);
                }
            }
            if ($kind === 'incident') {
                $this->requireSetup($company, 'factory', $data['details']['factory_code']);
            }
            if ($kind === 'training') {
                foreach ($data['details']['participants'] as $participant) {
                    $employee = $this->repository->employee($company, $participant['employee_full_id']);
                    if (! $employee) {
                        throw ValidationException::withMessages(['details.participants' => 'Every participant must belong to your company.']);
                    }
                    if ($data['status'] === 'planned' && $participant['result'] !== 'registered') {
                        throw ValidationException::withMessages(['details.participants' => 'Record attendance after completing the training.']);
                    }
                }
            }
            unset($data['id'], $data['version']);
            if ($record) {
                $record->fill($data);
                $record->version++;
                $record->save();
            } else {
                $record = $this->repository->records($company)->create($data + ['company_id' => $company, 'recorded_by' => $user->id, 'version' => 1]);
            }
            if ($kind === 'training') {
                $record->participants()->delete();
                $record->participants()->createMany(array_map(fn ($participant) => ['employee_full_id' => $participant['employee_full_id']], $data['details']['participants']));
            }
            $this->event($record, $actor, $user, $id ? 'updated' : 'created');

            return $record->load('files', 'events');
        });
    }

    public function currentShift(object $employee): ?array
    {
        $today = now('Asia/Dhaka')->toDateString();
        $roster = $this->repository->records((int) $employee->company_id)->where('kind', 'roster')
            ->where('employee_full_id', $employee->employee_full_id)->where('status', 'active')
            ->where('start_date', '<=', $today)->where('due_date', '>=', $today)->latest('id')->first();
        if (! $roster) {
            return null;
        }
        $shift = $this->factory->setup((int) $employee->company_id, 'shift', $roster->details['shift_code']);
        if (! $shift) {
            return null;
        }
        [$startHour, $startMinute] = array_map('intval', explode(':', $shift->details['start']));
        [$endHour, $endMinute] = array_map('intval', explode(':', $shift->details['end']));
        $duration = (($endHour * 60 + $endMinute) - ($startHour * 60 + $startMinute) + 1440) % 1440;

        return ['shift_name' => $shift->name, 'start_time' => $shift->details['start'], 'end_time' => $shift->details['end'],
            'break_minutes' => $shift->details['break_minutes'], 'standard_duty_hours' => ($duration - (int) $shift->details['break_minutes']) / 60,
            'factory_code' => $roster->details['factory_code'], 'line_code' => $roster->details['line_code'] ?? null,
            'assignment_start' => $roster->start_date, 'assignment_end' => $roster->due_date];
    }

    private function requireSetup(int $company, string $kind, string $code): object
    {
        $setup = $this->factory->setup($company, $kind, $code);
        if (! $setup) {
            throw ValidationException::withMessages(['details.'.$kind.'_code' => 'Choose an active '.$kind.' in your company.']);
        }

        return $setup;
    }

    private function event(HrRecord $record, object $actor, User $user, string $action): void
    {
        $record->events()->create(['actor_id' => $user->id, 'actor_name' => $actor->name, 'action' => $action,
            'status' => $record->status, 'version' => $record->version, 'created_at' => now()]);
    }

    public function attach(object $actor, User $user, int $id, UploadedFile $file): HrRecord
    {
        $record = $this->record($actor, $id);
        abort_unless($this->canManage($actor, $record->kind), 403, 'Attachment management access is not granted.');
        abort_unless($record->kind === 'document', 422, 'Files can be attached to worker documents.');
        $path = $file->store('hr-documents/'.$actor->company_id.'/'.$id, 'local');
        if (! $path) {
            throw ValidationException::withMessages(['file' => 'Private storage could not save this file.']);
        }
        try {
            return DB::transaction(function () use ($actor, $user, $id, $file, $path) {
                $record = $this->repository->record((int) $actor->company_id, $id, true);
                abort_if($record->files()->count() >= 20, 422, 'A document can hold up to 20 files.');
                $record->files()->create(['name' => mb_substr(basename($file->getClientOriginalName()), 0, 255), 'path' => $path, 'size' => $file->getSize()]);
                $record->version++;
                $record->save();
                $this->event($record, $actor, $user, 'file_added');

                return $record->load('files', 'events');
            });
        } catch (\Throwable $error) {
            Storage::disk('local')->delete($path);
            throw $error;
        }
    }

    public function download(object $actor, int $id, int $fileId): mixed
    {
        $record = $this->record($actor, $id);
        $file = $record->files()->findOrFail($fileId);
        abort_unless(Storage::disk('local')->exists($file->path), 404, 'This attachment is unavailable.');

        return Storage::disk('local')->download($file->path, $file->name, ['X-Content-Type-Options' => 'nosniff']);
    }
}
