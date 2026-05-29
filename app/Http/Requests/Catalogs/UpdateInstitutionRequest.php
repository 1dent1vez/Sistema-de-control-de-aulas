<?php

/**
 * @descripcion  FormRequest para validar la actualización de una institución.
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
 * @creado       2026-05-13
 *
 * @modificado   2026-05-13
 *
 * @cambios      2026-05-13 - Creación inicial del FormRequest
 */

declare(strict_types=1);

namespace App\Http\Requests\Catalogs;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInstitutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Policy admin-only en Fase 6
        return true;
    }

    public function rules(): array
    {
        $institutionId = $this->route('institutionId');

        return [
            'name' => ['string', 'max:100', Rule::unique('institutions', 'name')->ignore($institutionId, 'institution_id')],
            'code' => ['string', 'max:20', Rule::unique('institutions', 'code')->ignore($institutionId, 'institution_id')],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Ya existe una institución con ese nombre.',
            'code.unique' => 'Ya existe una institución con ese código.',
        ];
    }
}
