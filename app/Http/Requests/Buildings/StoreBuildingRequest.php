<?php

/**
 * @descripcion  FormRequest para validar la creación de un edificio.
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
 *               2026-05-25 - Actualización de validaciones (nombre alfanumérico + guion, niveles >= 1, descripción solo letras y sin estatus)
 */

declare(strict_types=1);

namespace App\Http\Requests\Buildings;

use App\Models\Institution;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBuildingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO Fase 6: Policy admin-only
        return true;
    }

    public function rules(): array
    {
        return [
            'institution_id' => ['nullable', 'integer', 'exists:gama_institutions,id'],
            'name' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9\-]+$/',
                'max:255',
                Rule::unique('gama_buildings', 'name')
                    ->where('institution_id', $this->input('institution_id')),
            ],
            'level_count' => ['required', 'integer', 'min:1', 'max:5'],
            'description' => ['nullable', 'string', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', 'max:500'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('institution_id') || ! $this->input('institution_id')) {
            $active = Institution::where('is_active', true)->first();
            abort_if(! $active, 422, 'No hay una institución activa registrada.');
            $this->merge(['institution_id' => $active->id]);
        } else {
            $institution = Institution::find($this->input('institution_id'));
            abort_if(! $institution?->is_active, 422, 'La institución seleccionada no está activa.');
        }
    }

    public function messages(): array
    {
        return [
            'institution_id.exists' => 'La institución seleccionada no existe.',
            'name.required' => 'El nombre del edificio es obligatorio.',
            'name.regex' => 'El nombre del edificio solo puede contener letras, números y el guion medio (-).',
            'name.unique' => 'Ya existe un edificio con ese nombre en la misma institución.',
            'level_count.required' => 'El número de niveles es obligatorio.',
            'level_count.min' => 'El edificio debe tener al menos 1 nivel.',
            'level_count.max' => 'El edificio no puede tener más de 5 niveles.',
            'description.regex' => 'La descripción solo puede contener letras.',
        ];
    }
}
