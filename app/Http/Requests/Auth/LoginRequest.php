<?php

/**
 * @descripcion  FormRequest para validar el login vía SAM.
 *
 * @autor        Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @mantenimiento Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @version      1.1.0
 *
 * @creado       2026-05-17
 *
 * @modificado   2026-05-26
 *
 * @cambios      2026-05-17 - Creación inicial del FormRequest
 *               2026-05-26 - Actualización de validaciones de formato y dominio de correo según RF-01
 */

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'captchaCode' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'El usuario es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
            'captchaCode.required' => 'El código de verificación es obligatorio.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $username = $this->input('username');
            if (empty($username)) {
                return;
            }

            // Si contiene '@' o parece que intentó ingresar un correo
            if (str_contains($username, '@')) {
                // Validar formato de correo
                if (! filter_var($username, FILTER_VALIDATE_EMAIL)) {
                    $validator->errors()->add('username', 'El usuario ingresado no tiene un formato valido.');

                    return;
                }

                // Validar dominio @toluca.tecnm.mx
                if (! str_ends_with(strtolower($username), '@toluca.tecnm.mx')) {
                    $validator->errors()->add('username', 'Solo se aceptan cuentas institucionales con dominio @toluca.tecnm.mx.');
                }
            }
        });
    }
}
