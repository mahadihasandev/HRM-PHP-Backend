<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['from_date' => 'required|date_format:Y-m-d', 'to_date' => 'required|date_format:Y-m-d|after_or_equal:from_date', 'leave_type_id' => 'required|integer|in:1,2,3,4', 'reason' => 'required|string|max:2000', 'emergency_phone' => 'nullable|string|max:50'];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['leave_type_id' => $this->input('leave_type_id', $this->input('leave_type'))]);
    }
}
