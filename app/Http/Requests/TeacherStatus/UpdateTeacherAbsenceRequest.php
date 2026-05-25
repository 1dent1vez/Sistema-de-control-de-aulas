<?php

/**
 * @descripcion  FormRequest para validar la actualización de una ausencia.
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
 * @creado       2026-05-14
 *
 * @modificado   2026-05-14
 *
 * @cambios      2026-05-14 - Creación inicial del FormRequest
 */

declare(strict_types=1);

namespace App\Http\Requests\TeacherStatus;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherAbsenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacher_external_id' => ['string', 'max:50'],
            'absence_type_id' => ['integer', 'exists:gama_absence_types,id'],
            'start_date' => ['date', 'before_or_equal:end_date', 'after_or_equal:today'],
            'end_date' => ['date', 'after_or_equal:start_date'],
            'observations' => ['nullable', 'string', 'max:500'],
            'is_confirmed' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_external_id.max' => 'El identificador del docente no debe exceder :max caracteres.',
            'absence_type_id.exists' => 'El tipo de ausencia seleccionado no existe.',
            'start_date.date' => 'La fecha de inicio no es válida.',
            'start_date.before_or_equal' => 'La fecha de inicio debe ser igual o anterior a la fecha de fin.',
            'start_date.after_or_equal' => 'La fecha de inicio no puede ser anterior al día de hoy.',
            'end_date.date' => 'La fecha de fin no es válida.',
            'end_date.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'observations.max' => 'Las observaciones no deben exceder :max caracteres.',
            'is_confirmed.boolean' => 'La confirmación debe ser verdadero o falso.',
        ];
    }
}
