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
 * @modificado   2026-05-13
 *
 * @cambios      2026-05-13 - Creación inicial del FormRequest
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
                'max:100',
                Rule::unique('gama_buildings', 'name')
                    ->where('institution_id', $this->input('institution_id')),
            ],
            'level_count' => ['required', 'integer', 'min:1', 'max:20'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('institution_id') || ! $this->input('institution_id')) {
            $first = Institution::first();
            $this->merge(['institution_id' => $first?->id ?? 1]);
        }
    }

    public function messages(): array
    {
        return [
            'institution_id.exists' => 'La institución seleccionada no existe.',
            'name.required' => 'El nombre del edificio es obligatorio.',
            'name.unique' => 'Ya existe un edificio con ese nombre en la misma institución.',
            'level_count.required' => 'El número de niveles es obligatorio.',
            'level_count.min' => 'El edificio debe tener al menos 1 nivel.',
            'level_count.max' => 'El edificio puede tener máximo 20 niveles.',
        ];
    }
}
