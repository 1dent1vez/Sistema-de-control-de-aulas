<?php

/**
 * @descripcion  FormRequest para validar la solicitud de descarga por lotes de códigos QR.
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
 * @modificado   2026-05-19
 *
 * @cambios      2026-05-19 - Estandarización de prólogo según formato GAMA
 */

declare(strict_types=1);

namespace App\Http\Requests\Qr;

use Illuminate\Foundation\Http\FormRequest;

class DownloadQrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classroom_ids' => ['required', 'array', 'min:1'],
            'classroom_ids.*' => ['required', 'integer', 'exists:gama_classrooms,id'],
            'format' => ['required', 'string', 'in:pdf,png'],
        ];
    }

    public function messages(): array
    {
        return [
            'classroom_ids.required' => 'La lista de aulas es obligatoria.',
            'classroom_ids.array' => 'La lista de aulas debe ser un arreglo.',
            'classroom_ids.min' => 'Debe seleccionar al menos un aula.',
            'classroom_ids.*.required' => 'Cada identificador de aula es obligatorio.',
            'classroom_ids.*.integer' => 'Cada identificador de aula debe ser un número entero.',
            'classroom_ids.*.exists' => 'Una o más aulas seleccionadas no existen.',
            'format.required' => 'El formato de descarga es obligatorio.',
            'format.in' => 'El formato debe ser pdf o png.',
        ];
    }
}
