<?php

/**
 * @descripcion  FormRequest para validar la consulta de solapamiento de ausencias.
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
 * @creado       2026-05-18
 *
 * @modificado   2026-05-18
 *
 * @cambios      2026-05-18 - Creación inicial del FormRequest
 */

declare(strict_types=1);

namespace App\Http\Requests\TeacherStatus;

use Illuminate\Foundation\Http\FormRequest;

class CheckOverlapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacher_external_id' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_external_id.required' => 'El identificador del docente es obligatorio.',
            'teacher_external_id.max' => 'El identificador del docente no debe exceder :max caracteres.',
            'start_date.required' => 'La fecha de inicio es obligatoria.',
            'start_date.date' => 'La fecha de inicio no es válida.',
            'end_date.required' => 'La fecha de fin es obligatoria.',
            'end_date.date' => 'La fecha de fin no es válida.',
            'end_date.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ];
    }
}
