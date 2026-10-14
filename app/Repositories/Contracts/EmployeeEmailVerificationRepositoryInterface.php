<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface EmployeeEmailVerificationRepositoryInterface
{
    public function lockRequester(int $userId): void;
    public function find(int $userId, string $email): ?object;
    public function save(int $userId, string $email, array $values): void;
    public function update(int $id, array $values): void;
    public function delete(int $id): void;
}
