<?php

/**
 * @descripcion  FormRequest para validar el código CAPTCHA antes del login SAM.
 *
 * @autor        Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @mantenimiento Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @version      1.0.0
 *
 * @creado       2026-05-18
 *
 * @modificado   2026-05-18
 *
 * @cambios      2026-05-18 - Creación inicial del FormRequest
 */

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ValidateCaptchaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'captchaCode' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'captchaCode.required' => 'El código de verificación es obligatorio.',
            'captchaCode.string' => 'El código de verificación debe ser texto.',
        ];
    }
}
