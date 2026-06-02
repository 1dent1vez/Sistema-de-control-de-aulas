<?php

/**
 * @descripcion  FormRequest para validar la asignación de rol a una identidad SAM.
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
 * @creado       2026-05-17
 *
 * @modificado   2026-05-17
 *
 * @cambios      2026-05-17 - Creación inicial del FormRequest
 */

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Enums\Auth\SamRole;
use App\Models\SamIdentity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\In;

class AssignRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', SamIdentity::class);
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', new In([SamRole::ADMIN->value, SamRole::TEACHER->value])],
            'current_password' => [
                'required_if:role,'.SamRole::ADMIN->value,
                'string',
                'min:6',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'El rol es obligatorio.',
            'role.in' => 'El rol debe ser uno de los siguientes: admin, teacher.',
            'current_password.required' => 'La contraseña del administrador actual es obligatoria para asignar el rol de administrador.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $externalId = $this->route('externalId');
            if ($externalId) {
                $samIdentity = SamIdentity::where('external_id', $externalId)->first();
                if ($samIdentity && $samIdentity->role === SamRole::ADMIN && $this->input('role') === SamRole::TEACHER->value) {
                    $validator->errors()->add('role', 'No puedes degradar un administrador a docente. Usa la opción de eliminar si es necesario.');

                    return;
                }
            }

            if ($this->input('role') === SamRole::ADMIN->value) {
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
            }
        });
    }
}
