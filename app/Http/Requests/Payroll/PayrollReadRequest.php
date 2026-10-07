<?php

declare(strict_types=1);

namespace App\Http\Requests\Payroll;

use App\Services\Payroll\PayrollAccess;
use Illuminate\Foundation\Http\FormRequest;

class PayrollReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        app(PayrollAccess::class)->actor($this->user());

        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
