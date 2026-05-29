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
 * @version      1.1.0
 *
 * @creado       2026-05-14
 *
 * @modificado   2026-05-25
 *
 * @cambios      2026-05-14 - Creación inicial del modelo
 *               2026-05-25 - Adición de la relación classSchedules (N:M).
 */

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherAbsence extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'teacher_absences';

    protected $primaryKey = 'teacher_absence_id';

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
        return $this->belongsTo(AbsenceType::class, 'absence_type_id', 'absence_type_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(SamIdentity::class, 'teacher_external_id', 'external_id');
    }

    public function classSchedules(): BelongsToMany
    {
        return $this->belongsToMany(
            ClassSchedule::class,
            'class_schedule_teacher_absence',
            'teacher_absence_id',
            'class_schedule_id'
        );
    }
}
