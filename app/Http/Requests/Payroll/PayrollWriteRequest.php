<?php

declare(strict_types=1);

namespace App\Http\Requests\Payroll;

use App\Services\Payroll\PayrollAccess;

class PayrollWriteRequest extends PayrollReadRequest
{
    public function authorize(): bool
    {
        app(PayrollAccess::class)->actor($this->user(), true);

        return true;
    }
}
