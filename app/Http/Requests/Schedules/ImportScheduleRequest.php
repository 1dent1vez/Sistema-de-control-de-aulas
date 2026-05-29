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
                Rule::exists('semesters', 'semester_id')->whereNull('deleted_at'),
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
                        if ((int) $value !== $semestreVigente->semester_id) {
                            $fail('El semestre ha caducado. No se pueden registrar ni modificar horarios.');
                        }
                    } catch (\Exception $e) {
                        Log::error('Error de BD al determinar el semestre vigente: '.$e->getMessage());
                        $fail('Error al consultar la base de datos. Intente nuevamente.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Debe seleccionar un archivo para importar.',
            'file.file' => 'Debe subir un archivo.',
            'file.mimes' => 'Solo se aceptan archivos con extension .csv o .xlsx.',
            'file.max' => 'El archivo es demasiado grande. El tamano maximo permitido es 5 MB.',
            'semester_id.required' => 'Debe existir un semestre vigente para registrar horarios.',
            'semester_id.exists' => 'No existe un semestre vigente. Cree un semestre antes de registrar horarios.',
        ];
    }
}
