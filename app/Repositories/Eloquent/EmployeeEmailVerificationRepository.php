<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\EmployeeEmailVerificationRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EmployeeEmailVerificationRepository implements EmployeeEmailVerificationRepositoryInterface
{
    public function lockRequester(int $userId): void
    {
        DB::table('users')->where('id', $userId)->lockForUpdate()->firstOrFail();
    }

    public function find(int $userId, string $email): ?object
    {
        return DB::table('employee_email_verifications')->where('requested_by', $userId)->where('email', $email)->lockForUpdate()->first();
    }

    public function save(int $userId, string $email, array $values): void
    {
        DB::table('employee_email_verifications')->updateOrInsert(['requested_by' => $userId, 'email' => $email], $values);
    }

    public function update(int $id, array $values): void
    {
        DB::table('employee_email_verifications')->where('id', $id)->update($values);
    }

    public function delete(int $id): void
    {
        DB::table('employee_email_verifications')->where('id', $id)->delete();
    }
}
