<?php

declare(strict_types=1);

namespace App\Http\Requests;

class VerifyEmployeeEmailRequest extends SendEmployeeVerificationRequest
{
    public function rules(): array
    {
        return parent::rules() + ['code' => ['required', 'string', 'regex:/^[0-9]{6}$/']];
    }
}
