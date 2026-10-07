<?php

declare(strict_types=1);

namespace App\Http\Requests\Payroll;

class PayrollAttachmentRequest extends PayrollActionRequest
{
    public function rules(): array
    {
        return ['file' => 'required|file|max:10240|mimes:pdf,xlsx,csv,txt,json,jpg,jpeg,png,docx'];
    }
}
