<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\PeopleService;
use Illuminate\Foundation\Http\FormRequest;

class PeopleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        app(PeopleService::class)->authorizeWrite($this->user(), (string) $this->input('kind'));

        return true;
    }

    public function rules(): array
    {
        $base = [
            'kind' => 'required|in:document,roster,training,grievance,incident,holiday',
            'id' => 'nullable|integer|min:1', 'version' => 'required_with:id|integer|min:1',
            'employee_full_id' => 'nullable|string|max:255', 'title' => 'required|string|max:255',
            'start_date' => 'required|date_format:Y-m-d',
            'due_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ];
        if (in_array($this->input('kind'), ['grievance', 'incident'], true)) {
            $base['start_date'] .= '|before_or_equal:'.now('Asia/Dhaka')->toDateString();
        }

        return array_merge($base, match ($this->input('kind')) {
            'document' => [
                'employee_full_id' => 'required|string|max:255', 'status' => 'required|in:pending,verified,expired,archived',
                'details' => 'required|array:category,reference,notes',
                'details.category' => 'required|in:appointment,contract,id,certificate,other',
                'details.reference' => 'nullable|string|max:255', 'details.notes' => 'nullable|string|max:5000',
            ],
            'roster' => [
                'employee_full_id' => 'required|string|max:255', 'due_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
                'status' => 'required|in:active,cancelled', 'details' => 'required|array:factory_code,shift_code,line_code,notes',
                'details.factory_code' => 'required|string|max:50', 'details.shift_code' => 'required|string|max:50',
                'details.line_code' => 'nullable|string|max:50', 'details.notes' => 'nullable|string|max:5000',
            ],
            'training' => [
                'status' => 'required|in:planned,completed,cancelled',
                'details' => 'required|array:category,trainer,location,participants,notes',
                'details.category' => 'required|in:induction,fire_safety,first_aid,skills,worker_rights,harassment_prevention,other',
                'details.trainer' => 'required|string|max:255', 'details.location' => 'required|string|max:255',
                'details.notes' => 'nullable|string|max:5000', 'details.participants' => 'required|array|min:1|max:500',
                'details.participants.*' => 'required|array:employee_full_id,result',
                'details.participants.*.employee_full_id' => 'required|string|max:255|distinct',
                'details.participants.*.result' => 'required|in:registered,attended,absent',
            ],
            'grievance' => [
                'status' => 'required|in:open,in_review,resolved',
                'details' => 'required|array:category,priority,description,resolution',
                'details.category' => 'required|in:wages,harassment,safety,welfare,other',
                'details.priority' => 'required|in:normal,high,urgent',
                'details.description' => 'required|string|max:5000',
                'details.resolution' => 'nullable|string|max:5000|required_if:status,resolved',
            ],
            'incident' => [
                'status' => 'required|in:open,in_progress,completed',
                'details' => 'required|array:category,severity,description,corrective_action,responsible_person,factory_code',
                'details.category' => 'required|in:near_miss,injury,fire,equipment,other',
                'details.severity' => 'required|in:low,medium,high,critical',
                'details.description' => 'required|string|max:5000',
                'details.factory_code' => 'required|string|max:50',
                'details.responsible_person' => 'nullable|string|max:255|required_if:status,completed',
                'details.corrective_action' => 'nullable|string|max:5000|required_if:status,completed',
            ],
            'holiday' => [
                'status' => 'required|in:scheduled,cancelled', 'details' => 'required|array:category,paid,notes',
                'details.category' => 'required|in:public,festival,weekly,company',
                'details.paid' => 'required|boolean', 'details.notes' => 'nullable|string|max:5000',
            ],
            default => ['status' => 'required|string', 'details' => 'required|array'],
        });
    }
}
