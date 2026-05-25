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
use App\Models\Semester;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
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
            'semester_id' => [
                'required',
                'integer',
                Rule::exists('gama_semesters', 'id')->whereNull('deleted_at'),
                function ($attribute, $value, $fail) {
                    try {
                        $today = now()->format('Y-m-d');
                        $semesters = Semester::vigente($today)->get();

                        if ($semesters->isEmpty()) {
                            $fail('No existe semestre vigente');

                            return;
                        }

                        if ($semesters->count() > 1) {
                            Log::critical('Error crítico: Existe más de un semestre vigente simultáneamente.');
                        }

                        $semestreVigente = $semesters->first();
                        if ((int) $value !== $semestreVigente->id) {
                            $fail('El semestre seleccionado no está vigente.');
                        }
                    } catch (\Exception $e) {
                        Log::error('Error de BD al determinar el semestre vigente: '.$e->getMessage());
                        $fail('Error al determinar el semestre vigente. No se puede registrar el horario.');
                    }
                },
            ],
            'classroom_id' => ['required', 'integer', 'exists:gama_classrooms,id'],
            'teacher_external_id' => ['required', 'string', 'max:50'],
            'subject_name' => ['required', 'string', 'max:100'],
            'group_name' => ['required', 'string', 'max:10'],
            'weekday' => ['required', 'string', Rule::in(Weekday::values())],
            'start_time' => ['required', 'date_format:H:i', 'before:end_time', 'regex:/^\d{2}:00$/'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time', 'regex:/^\d{2}:00$/'],
            'status' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'semester_id.required' => 'El semestre es obligatorio.',
            'semester_id.exists' => 'El semestre seleccionado no existe.',
            'classroom_id.required' => 'El aula es obligatoria.',
            'classroom_id.exists' => 'El aula seleccionada no existe.',
            'teacher_external_id.required' => 'El identificador del docente es obligatorio.',
            'subject_name.required' => 'El nombre de la materia es obligatorio.',
            'subject_name.max' => 'El nombre de la materia no debe exceder :max caracteres.',
            'group_name.required' => 'El nombre del grupo es obligatorio.',
            'group_name.max' => 'El nombre del grupo no debe exceder :max caracteres.',
            'weekday.required' => 'El día de la semana es obligatorio.',
            'weekday.in' => 'El día de la semana no es válido.',
            'start_time.required' => 'La hora de inicio es obligatoria.',
            'start_time.date_format' => 'La hora de inicio debe tener formato HH:MM.',
            'start_time.before' => 'La hora de inicio debe ser anterior a la hora de fin.',
            'start_time.regex' => 'La hora de inicio debe ser una hora completa (minutos :00).',
            'end_time.required' => 'La hora de fin es obligatoria.',
            'end_time.date_format' => 'La hora de fin debe tener formato HH:MM.',
            'end_time.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
            'end_time.regex' => 'La hora de fin debe ser una hora completa (minutos :00).',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Acepta group_label (alias del frontend) como group_name
        if ($this->has('group_label') && ! $this->has('group_name')) {
            $this->merge(['group_name' => $this->input('group_label')]);
        }
    }
}
