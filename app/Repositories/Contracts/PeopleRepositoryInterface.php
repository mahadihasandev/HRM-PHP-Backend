<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\HrRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface PeopleRepositoryInterface
{
    public function records(int $companyId): Builder;

    public function record(int $companyId, int $id, bool $lock = false): HrRecord;

    public function employees(int $companyId): Collection;

    public function employee(int $companyId, string $fullId, bool $lock = false): ?object;
}
