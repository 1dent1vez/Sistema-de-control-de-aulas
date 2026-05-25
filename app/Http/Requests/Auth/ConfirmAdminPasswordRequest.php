<?php

/**
 * @descripcion  FormRequest para confirmar la contraseña local del administrador actual.
 *
 * @autor        Antigravity <support@google.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Antigravity <support@google.com>
 *
 * @mantenimiento Antigravity <support@google.com>
 *
 * @version      1.0.0
 *
 * @creado       2026-05-24
 *
 * @modificado   2026-05-24
 */

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class ConfirmAdminPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'La contraseña de administrador es obligatoria.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            if (! $user) {
                return;
            }
            if ($user->password === null) {
                $validator->errors()->add('current_password', 'Debes configurar una contraseña de administrador antes de realizar esta acción.');

                return;
            }
            if (! Hash::check($this->input('current_password'), $user->password)) {
                $validator->errors()->add('current_password', 'Contraseña de administrador incorrecta.');
            }
        });
    }
}
