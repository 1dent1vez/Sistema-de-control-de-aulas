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
 * @version      1.1.0
 *
 * @creado       2026-05-13
 *
 * @modificado   2026-05-26
 *
 * @cambios      2026-05-13 - Creación inicial del FormRequest
 *               2026-05-26 - Actualización de validaciones de semestre y mensajes de error en español según requerimientos.
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
                            $fail('No existe un semestre vigente. Cree un semestre antes de registrar horarios.');

                            return;
                        }

                        if ($semesters->count() > 1) {
                            Log::critical('Error crítico: Existe más de un semestre vigente simultáneamente.');
                        }

                        $semestreVigente = $semesters->first();
                        if ((int) $value !== $semestreVigente->id) {
                            $fail('El semestre ha caducado. No se pueden registrar ni modificar horarios.');
                        }
                    } catch (\Exception $e) {
                        Log::error('Error de BD al determinar el semestre vigente: '.$e->getMessage());
                        $fail('Error al consultar la base de datos. Intente nuevamente.');
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
            'semester_id.required' => 'Debe existir un semestre vigente para registrar horarios.',
            'semester_id.exists' => 'No existe un semestre vigente. Cree un semestre antes de registrar horarios.',
            'classroom_id.required' => 'El aula es obligatoria.',
            'classroom_id.exists' => 'El aula seleccionada no existe o no esta disponible.',
            'teacher_external_id.required' => 'El docente es obligatorio.',
            'subject_name.required' => 'El nombre de la materia es obligatorio.',
            'subject_name.max' => 'El nombre de la materia no debe exceder :max caracteres.',
            'group_name.required' => 'El grupo es obligatorio.',
            'group_name.max' => 'El nombre del grupo no debe exceder :max caracteres.',
            'weekday.required' => 'El dia de la semana es obligatorio.',
            'weekday.in' => 'El dia de la semana no es valido. Use: Lunes, Martes, Miercoles, Jueves, Viernes, Sabado o Domingo.',
            'start_time.required' => 'La hora de inicio es obligatoria.',
            'start_time.date_format' => 'El formato de hora no es valido. Use HH:MM (ejemplo: 08:00).',
            'start_time.before' => 'La hora de inicio debe ser menor que la hora de fin.',
            'start_time.regex' => 'La hora de inicio debe ser una hora completa (minutos :00).',
            'end_time.required' => 'La hora de fin es obligatoria.',
            'end_time.date_format' => 'El formato de hora no es valido. Use HH:MM (ejemplo: 10:00).',
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
