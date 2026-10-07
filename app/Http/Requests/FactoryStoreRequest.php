<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\FactoryService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class FactoryStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        app(FactoryService::class)->actor($this->user(), true);

        return true;
    }

    public function rules(): array
    {
        $base = ['type' => 'required|in:setup,production,safety', 'id' => 'nullable|integer|min:1'];
        if ($this->input('type') === 'setup') {
            $rules = ['kind' => 'required|in:factory,line,shift,grade', 'code' => 'required|string|max:50|regex:/^[A-Za-z0-9_-]+$/', 'name' => 'required|string|max:255', 'active' => 'required|boolean'];
            $detailRules = match ($this->input('kind')) {
                'factory' => ['details' => 'required|array:address', 'details.address' => 'nullable|string|max:255'],
                'line' => ['details' => 'required|array:factory_code,section,capacity', 'details.factory_code' => 'required|string|max:50', 'details.section' => 'required|string|max:255', 'details.capacity' => 'required|integer|min:1|max:100000'],
                'shift' => ['details' => 'required|array:start,end,break_minutes', 'details.start' => 'required|date_format:H:i', 'details.end' => 'required|date_format:H:i|different:details.start', 'details.break_minutes' => 'required|integer|min:0|max:480'],
                'grade' => ['details' => 'required|array:basic_salary,overtime_rate', 'details.basic_salary' => 'required|numeric|min:0|max:10000000|decimal:0,2', 'details.overtime_rate' => 'required|numeric|min:0|max:10000000|decimal:0,2'],
                default => [],
            };

            return $base + $rules + $detailRules;
        }
        $common = ['date' => 'required|date_format:Y-m-d', 'factory_code' => 'required|string|max:50'];

        return $base + $common + ($this->input('type') === 'production' ? [
            'line_code' => 'required|string|max:50', 'order_ref' => 'required|string|max:255', 'style' => 'required|string|max:255', 'target' => 'required|integer|min:1|max:10000000', 'completed' => 'required|integer|min:0|max:10000000', 'rejected' => 'required|integer|min:0|lte:completed',
        ] : ['category' => 'required|in:safety,training,maintenance', 'title' => 'required|string|max:255', 'notes' => 'nullable|string|max:5000', 'owner' => 'required|string|max:255', 'status' => 'required|in:open,in_progress,completed']);
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty() || $this->input('type') !== 'setup' || $this->input('kind') !== 'shift') {
                return;
            }
            [$startHour, $startMinute] = array_map('intval', explode(':', $this->input('details.start')));
            [$endHour, $endMinute] = array_map('intval', explode(':', $this->input('details.end')));
            $duration = (($endHour * 60 + $endMinute) - ($startHour * 60 + $startMinute) + 1440) % 1440;
            if ((int) $this->input('details.break_minutes') >= $duration) {
                $validator->errors()->add('details.break_minutes', 'Break time must be shorter than the shift duration.');
            }
        }];
    }
}
