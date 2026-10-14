<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class EmployeeEmailVerificationData
{
    public function __construct(public int $userId, public string $email, public string $code = '') {}
}
