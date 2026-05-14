<?php

/**
 * @descripcion  Modelo Eloquent para la entidad TeacherAbsence.
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
 * @cambios      2026-05-14 - Creación inicial del modelo
 */

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherAbsence extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'gama_teacher_absences';

    protected $fillable = [
        'teacher_external_id',
        'absence_type_id',
        'start_date',
        'end_date',
        'observations',
        'is_confirmed',
    ];

    protected function casts(): array
    {
        return [
            'is_confirmed' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function absenceType(): BelongsTo
    {
        return $this->belongsTo(AbsenceType::class);
    }
}
