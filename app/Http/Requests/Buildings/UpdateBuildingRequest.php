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
 * @modificado   2026-05-25
 *
 * @cambios      2026-05-13 - Creación inicial del FormRequest
 *               2026-05-25 - Actualización de validaciones (nombre alfanumérico + guion, descripción solo letras y sin estatus)
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
                'regex:/^[a-zA-Z0-9\-]+$/',
                'max:255',
                Rule::unique('buildings', 'name')
                    ->ignore($buildingId, 'building_id'),
            ],
            'description' => ['nullable', 'string', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'El nombre del edificio solo puede contener letras, números y el guion medio (-).',
            'name.unique' => 'Ya existe un edificio con ese nombre.',
            'description.regex' => 'La descripción solo puede contener letras.',
        ];
    }
}
