<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Services\Payroll\PayrollRunService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollRunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $rows = ($this->relationLoaded('items') ? $this->items : collect())->map(function ($item) {
            $row = $item->only(array_merge(['id', 'employee_full_id', 'employee_name', 'department', 'designation', 'factory', 'section', 'line', 'grade', 'bank_name', 'bank_account_no', 'routing_no', 'payment_method'], PayrollRunService::EARNINGS, PayrollRunService::DEDUCTIONS, ['overtime_hours', 'overtime_rate', 'overtime_amount', 'total_earnings', 'total_deductions', 'net_payable']));
            foreach (array_merge(PayrollRunService::EARNINGS, PayrollRunService::DEDUCTIONS, ['overtime_hours', 'overtime_rate', 'overtime_amount', 'total_earnings', 'total_deductions', 'net_payable']) as $key) {
                $row[$key] = (float) $row[$key];
            }

            return $row;
        })->all();

        return $this->resource->only(['id', 'company_name', 'company_address', 'month', 'title', 'status', 'source_name', 'created_by', 'approved_by', 'bank_name', 'bank_branch', 'debit_account', 'signatory', 'signatory_title', 'payment_reference']) + [
            'approved_at' => $this->approved_at?->toIso8601String(), 'payment_date' => $this->payment_date?->format('Y-m-d'), 'created_at' => $this->created_at?->toIso8601String(),
            'items' => $rows, 'summary' => $this->relationLoaded('items') ? app(PayrollRunService::class)->summary($rows) : [
                'employees' => (int) $this->items_count, 'total_earnings' => (float) $this->items_sum_total_earnings,
                'total_deductions' => (float) $this->items_sum_total_deductions, 'net_payable' => (float) $this->items_sum_net_payable,
                'overtime_amount' => (float) $this->items_sum_overtime_amount, 'bank_total' => (float) $this->bank_total, 'cash_total' => (float) $this->cash_total,
            ],
            'attachments' => ($this->relationLoaded('attachments') ? $this->attachments : collect())->map(fn ($file) => $file->only(['id', 'name', 'size', 'created_at']))->all(),
        ];
    }
}
