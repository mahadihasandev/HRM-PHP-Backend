<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LegacyPayslipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = (array) $this->resource;
        foreach (['basic_salary', 'house_rent', 'medical_allowance', 'conveyance', 'special_allowance', 'overtime_hours', 'overtime_rate', 'overtime_amount', 'total_earnings', 'pf_deduction', 'tax_deduction', 'loan_deduction', 'other_deductions', 'total_deductions', 'net_payable'] as $field) {
            $data[$field] = (float) ($data[$field] ?? 0);
        }

        return $data;
    }
}
