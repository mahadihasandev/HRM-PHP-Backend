<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\PeopleService;
use Illuminate\Foundation\Http\FormRequest;

class PeopleReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        app(PeopleService::class)->actor($this->user());

        return true;
    }

    public function rules(): array
    {
        return ['kind' => 'sometimes|in:document,roster,training,grievance,incident,holiday',
            'q' => 'nullable|string|max:100', 'page' => 'sometimes|integer|min:1',
            'status' => 'nullable|string|max:30'];
    }
}
