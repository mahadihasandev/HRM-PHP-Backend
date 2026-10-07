<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface FactoryRepositoryInterface
{
    public function list(int $companyId, string $type): Collection;

    public function save(int $companyId, string $type, array $data, ?int $id = null): Model;

    public function setup(int $companyId, string $kind, string $code): ?Model;
}
