<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\FactoryRepositoryInterface;
use App\Repositories\Contracts\PayrollRepositoryInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;

class FactoryService extends BaseService
{
    public function __construct(private FactoryRepositoryInterface $repository, private PayrollRepositoryInterface $employees) {}

    public function actor(User $user, bool $write = false): object
    {
        $actor = $this->employees->actor($user);
        abort_unless($actor && $actor->status === 'Active' && $this->employees->allowed($actor, 'module.factory'), 403, 'Factory operations access is not granted.');
        if ($write) {
            abort_unless($this->employees->allowed($actor, 'action.employees.edit'), 403, 'Factory management access is not granted.');
        }

        return $actor;
    }

    public function dashboard(object $actor): array
    {
        $companyId = (int) $actor->company_id;

        return ['setups' => $this->repository->list($companyId, 'setup'), 'production' => $this->repository->list($companyId, 'production'), 'safety' => $this->repository->list($companyId, 'safety')];
    }

    public function save(object $actor, User $user, array $data): mixed
    {
        $companyId = (int) $actor->company_id;
        $type = $data['type'];
        $id = $data['id'] ?? null;
        unset($data['type'], $data['id']);
        if ($type === 'setup' && $data['kind'] === 'line') {
            $this->requireFactory($companyId, $data['details']['factory_code']);
        }
        if ($type !== 'setup') {
            $this->requireFactory($companyId, $data['factory_code']);
            $data['recorded_by'] = $user->id;
        }
        if ($type === 'production') {
            $line = $this->repository->setup($companyId, 'line', $data['line_code']);
            if (! $line || $line->details['factory_code'] !== $data['factory_code']) {
                throw ValidationException::withMessages(['line_code' => 'Choose an active line belonging to this factory.']);
            }
        }
        try {
            return $this->repository->save($companyId, $type, $data, $id);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['record' => 'This code or daily line/order record already exists. Edit the existing record.']);
        }
    }

    private function requireFactory(int $companyId, string $code): void
    {
        if (! $this->repository->setup($companyId, 'factory', $code)) {
            throw ValidationException::withMessages(['factory_code' => 'Choose an active factory from setup.']);
        }
    }
}
