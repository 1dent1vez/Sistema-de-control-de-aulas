<?php

/**
 * @descripcion  FormRequest para validar la importación de horarios.
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

namespace App\Http\Requests\Schedules;

use Illuminate\Foundation\Http\FormRequest;

class ImportScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,xlsx', 'max:10240'],
            'semester_id' => ['required', 'integer', 'exists:gama_semesters,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'El archivo es obligatorio.',
            'file.file' => 'Debe subir un archivo.',
            'file.mimes' => 'El archivo debe ser CSV o XLSX.',
            'file.max' => 'El archivo no debe exceder :max kilobytes.',
            'semester_id.required' => 'El semestre es obligatorio.',
            'semester_id.exists' => 'El semestre seleccionado no existe.',
        ];
    }
}
