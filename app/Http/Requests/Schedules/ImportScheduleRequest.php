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

use App\Models\Semester;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ImportScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,xlsx', 'max:5120'],
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
