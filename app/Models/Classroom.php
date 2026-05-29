<?php

/**
 * @descripcion  Modelo Eloquent para la entidad Classroom (gama_classrooms).
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
 * @cambios      2026-05-13 - Creación inicial del modelo
 */

declare(strict_types=1);

namespace App\Models;

use App\Enums\Buildings\ClassroomType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classroom extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'classrooms';

    protected $primaryKey = 'classroom_id';

    protected $fillable = [
        'building_id',
        'level_id',
        'classroom_name',
        'classroom_type',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'classroom_type' => ClassroomType::class,
        ];
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id', 'building_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id', 'level_id');
    }

    public function activeQr(): HasOne
    {
        return $this->hasOne(QrCode::class, 'classroom_id', 'classroom_id')->where('is_active', true);
    }

    public function classSchedules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ClassSchedule::class, 'classroom_id', 'classroom_id');
    }
}

