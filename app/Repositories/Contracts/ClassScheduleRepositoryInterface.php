<?php

/**
 * @descripcion  Interfaz del repositorio de horarios.
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
 * @cambios      2026-05-13 - Creación inicial de la interfaz
 */

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\ClassSchedule;
use Illuminate\Database\Eloquent\Collection;

interface ClassScheduleRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function findById(int $id): ?ClassSchedule;

    public function create(array $data): ClassSchedule;

    public function update(ClassSchedule $schedule, array $data): ClassSchedule;

    public function delete(ClassSchedule $schedule): bool;

    public function hasOverlap(int $classroomId, string $weekday, string $startTime, string $endTime, ?int $excludeId = null): bool;

    public function insertMultiple(array $data): bool;
}
