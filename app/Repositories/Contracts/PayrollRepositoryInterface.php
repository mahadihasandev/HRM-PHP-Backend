<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\PayrollRun;
use App\Models\User;
use Illuminate\Support\Collection;

interface PayrollRepositoryInterface
{
    public function actor(User $user): ?object;

    public function allowed(object $actor, string $permission): bool;

    public function employees(int $companyId): Collection;

    public function existingEmployees(int $companyId, string $month): array;

    public function history(int $companyId): Collection;

    public function runs(int $companyId): Collection;

    public function run(int $companyId, int $id, bool $lock = false): PayrollRun;

    public function create(array $attributes, array $rows): PayrollRun;
}
