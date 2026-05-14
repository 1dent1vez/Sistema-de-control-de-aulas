<?php

/**
 * @descripcion  Interfaz del repositorio de ausencias de docentes.
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
 * @cambios      2026-05-14 - Creación inicial de la interfaz
 */

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\TeacherAbsence;
use Illuminate\Database\Eloquent\Collection;

interface TeacherAbsenceRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function findById(int $id): ?TeacherAbsence;

    public function create(array $data): TeacherAbsence;

    public function update(TeacherAbsence $absence, array $data): TeacherAbsence;

    public function delete(TeacherAbsence $absence): bool;

    public function findOverlappingAbsences(string $teacherExternalId, string $startDate, string $endDate, ?int $excludeId = null): Collection;
}
