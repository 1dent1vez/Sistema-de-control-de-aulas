<?php

/**
 * @descripcion  FormRequest para validar la creación de un horario.
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

use App\Enums\Schedules\Weekday;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'semester_id' => ['required', 'integer', 'exists:gama_semesters,id'],
            'classroom_id' => ['required', 'integer', 'exists:gama_classrooms,id'],
            'teacher_external_id' => ['required', 'string', 'max:50'],
            'subject_name' => ['required', 'string', 'max:100'],
            'group_name' => ['required', 'string', 'max:10'],
            'weekday' => ['required', 'string', Rule::in(Weekday::values())],
            'start_time' => ['required', 'date_format:H:i', 'before:end_time'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'status' => ['boolean'],
        ];
    }
}
