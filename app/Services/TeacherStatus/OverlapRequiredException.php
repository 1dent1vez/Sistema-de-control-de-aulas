<?php

/**
 * @descripcion  Excepción lanzada cuando se detecta traslape de ausencias y requiere confirmación.
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
 * @cambios      2026-05-14 - Creación inicial de la excepción
 */

declare(strict_types=1);

namespace App\Services\TeacherStatus;

use App\Models\TeacherAbsence;
use Illuminate\Database\Eloquent\Collection;

class OverlapRequiredException extends \RuntimeException
{
    /**
     * @param  Collection<int, TeacherAbsence>  $overlaps  Ausencias con las que se traslapa
     */
    public function __construct(
        string $message,
        private readonly Collection $overlaps,
    ) {
        parent::__construct($message);
    }

    /**
     * Obtiene la colección de ausencias traslapadas.
     *
     * @return Collection<int, TeacherAbsence>
     */
    public function getOverlaps(): Collection
    {
        return $this->overlaps;
    }

    /**
     * Obtiene los detalles de traslape como array de strings legibles.
     *
     * @return array<int, string>
     */
    public function getOverlapDetails(): array
    {
        return $this->overlaps->map(function (TeacherAbsence $absence): string {
            $type = $absence->absenceType?->name ?? 'Desconocido';

            return "Ausencia existente {$type} del {$absence->start_date->format('Y-m-d')} al {$absence->end_date->format('Y-m-d')}";
        })->toArray();
    }
}
