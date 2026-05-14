<?php

declare(strict_types=1);

namespace App\Http\Requests\Qr;

use Illuminate\Foundation\Http\FormRequest;

class GenerateQrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'force_regenerate' => ['sometimes', 'boolean'],
        ];
    }
}
