<?php

declare(strict_types=1);

namespace App\Http\Requests\Payroll;

use App\Services\Payroll\PayrollAccess;
use Illuminate\Foundation\Http\FormRequest;

class PayrollActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        app(PayrollAccess::class)->actor($this->user(), true);

        return true;
    }

    public function rules(): array
    {
        return ['action' => 'required|in:approve,paid', 'payment_date' => 'required_if:action,paid|nullable|date_format:Y-m-d', 'payment_reference' => 'required_if:action,paid|nullable|string|max:255'];
    }
}
