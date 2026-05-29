<?php

/**
 * @descripcion  Interfaz del repositorio de semestres.
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

use App\Models\Semester;
use Illuminate\Database\Eloquent\Collection;

interface SemesterRepositoryInterface
{
    public function all(): Collection;

    public function findById(int $id): ?Semester;

    public function getCurrent(): ?Semester;

    public function hasOverlap(?int $institutionId, string $startDate, string $endDate, ?int $excludeId = null): bool;

    public function create(array $data): Semester;

    public function update(Semester $semester, array $data): Semester;

    public function delete(Semester $semester): bool;

    public function getExpired(): Collection;
}
