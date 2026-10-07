<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\FactoryService;
use Illuminate\Foundation\Http\FormRequest;

class FactoryReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        app(FactoryService::class)->actor($this->user());

        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
