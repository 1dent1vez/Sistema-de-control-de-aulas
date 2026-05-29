<?php

/**
 * @descripcion  FormRequest para validar la creación de un aula.
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

use App\Enums\Buildings\ClassroomType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO Fase 6: Policy admin-only
        return true;
    }

    public function rules(): array
    {
        $buildingId = $this->input('building_id');

        return [
            'building_id' => ['required', 'integer', 'exists:buildings,building_id'],
            'level_id' => [
                'required',
                'integer',
                'exists:levels,level_id',
            ],
            'classroom_name' => [
                'required',
                'string',
                'max:30',
                Rule::unique('classrooms', 'classroom_name')
                    ->where('building_id', $buildingId),
            ],
            'classroom_type' => ['required', 'string', Rule::in(ClassroomType::values())],
            'status' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'building_id.required' => 'El edificio es obligatorio.',
            'building_id.exists' => 'El edificio seleccionado no existe.',
            'level_id.required' => 'El nivel es obligatorio.',
            'level_id.exists' => 'El nivel seleccionado no existe en el edificio indicado.',
            'classroom_name.required' => 'El nombre del aula es obligatorio.',
            'classroom_name.unique' => 'Ya existe un aula con ese nombre en el mismo edificio.',
            'classroom_type.required' => 'El tipo de aula es obligatorio.',
            'classroom_type.in' => 'El tipo de aula debe ser classroom o computer_lab.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->mergeIfMissing([
            'status' => true,
        ]);
    }
}
