<?php

declare(strict_types=1);

namespace App\Http\Requests\Payroll;

use App\Services\Payroll\PayrollAccess;
use Illuminate\Foundation\Http\FormRequest;

class PayrollImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        app(PayrollAccess::class)->actor($this->user(), true);

        return true;
    }

    public function rules(): array
    {
        // Row-level errors are reported together by the preview service.
        $rules = ['month' => ['required', 'date_format:Y-m'], 'title' => 'required|string|max:255', 'source_name' => 'nullable|string|max:255', 'rows' => 'required|array|list|min:1|max:5000', 'rows.*' => 'required|array'];
        foreach (['company_address', 'bank_name', 'bank_branch', 'debit_account', 'signatory', 'signatory_title'] as $field) {
            $rules[$field] = 'nullable|string|max:255';
        }

        return $rules;
    }
}
