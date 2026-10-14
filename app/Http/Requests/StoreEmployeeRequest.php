<?php

declare(strict_types=1);

namespace App\Http\Requests;

class StoreEmployeeRequest extends SendEmployeeVerificationRequest
{
    public function rules(): array
    {
        return parent::rules() + [
            'name' => 'required|string|max:150',
            'designation' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|string|min:8',
            'basic_salary' => 'required|numeric|min:0|max:999999999.99',
            'bank_account_no' => ['nullable', 'string', 'regex:/^[0-9]{5,34}$/'],
            'joining_date' => 'required|date',
            'email_verification_token' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/'],
        ];
    }
}
