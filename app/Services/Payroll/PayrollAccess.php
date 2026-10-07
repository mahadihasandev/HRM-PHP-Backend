<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Models\User;
use App\Repositories\Contracts\PayrollRepositoryInterface;
use App\Services\BaseService;

class PayrollAccess extends BaseService
{
    public function __construct(private PayrollRepositoryInterface $repository) {}

    public function actor(User $user, bool $write = false): object
    {
        $actor = $this->repository->actor($user);
        abort_unless($actor && $actor->status === 'Active', 403, 'An active employee account is required.');
        abort_unless($this->repository->allowed($actor, 'module.salary'), 403, 'Payroll access is not granted.');
        if ($write) {
            abort_unless($this->repository->allowed($actor, 'action.salary.disburse'), 403, 'Payroll management access is not granted.');
        }

        return $actor;
    }
}
