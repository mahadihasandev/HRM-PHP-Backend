<?php

declare(strict_types=1);

namespace App\Http\Requests;

class PeopleFileRequest extends PeopleReadRequest
{
    public function rules(): array
    {
        return ['file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,docx,txt'];
    }
}
