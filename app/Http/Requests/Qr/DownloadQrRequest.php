<?php

declare(strict_types=1);

namespace App\Http\Requests\Qr;

use Illuminate\Foundation\Http\FormRequest;

class DownloadQrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classroom_ids' => ['required', 'array', 'min:1'],
            'classroom_ids.*' => ['required', 'integer', 'exists:gama_classrooms,id'],
            'format' => ['required', 'string', 'in:pdf,png'],
        ];
    }
}
