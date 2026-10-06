<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    /**
     * Get all records.
     *
     * @param array<string> $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Find a record by ID.
     *
     * @param int|string $id
     * @param array<string> $columns
     * @return Model|null
     */
    public function find(int|string $id, array $columns = ['*']): ?Model;

    /**
     * Find a record or fail.
     *
     * @param int|string $id
     * @param array<string> $columns
     * @return Model
     */
    public function findOrFail(int|string $id, array $columns = ['*']): Model;

    /**
     * Create a new record.
     *
     * @param array<string, mixed> $attributes
     * @return Model
     */
    public function create(array $attributes): Model;

    /**
     * Update an existing record.
     *
     * @param int|string $id
     * @param array<string, mixed> $attributes
     * @return Model
     */
    public function update(int|string $id, array $attributes): Model;

    /**
     * Delete a record by ID.
     *
     * @param int|string $id
     * @return bool
     */
    public function delete(int|string $id): bool;

    /**
     * Paginate records.
     *
     * @param int $perPage
     * @param array<string> $columns
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;
}
