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
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\In;

class AssignRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', new In(SamRole::values())],
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'El rol es obligatorio.',
            'role.in' => 'El rol debe ser admin o teacher.',
        ];
    }
}
