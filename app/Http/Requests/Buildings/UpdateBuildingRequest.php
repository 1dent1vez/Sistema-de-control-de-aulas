<?php

/**
 * @descripcion  FormRequest para validar la actualización de un edificio.
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

namespace App\Http\Requests\Buildings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBuildingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO Fase 6: Policy admin-only
        return true;
    }

    public function rules(): array
    {
        $buildingId = $this->route('buildingId');

        return [
            'name' => [
                'string',
                'max:100',
                Rule::unique('gama_buildings', 'name')
                    ->where('institution_id', $this->input('institution_id'))
                    ->ignore($buildingId),
            ],
            'status' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Ya existe un edificio con ese nombre en la misma institución.',
        ];
    }
}
