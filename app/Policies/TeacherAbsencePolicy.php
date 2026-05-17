<?php

/**
 * @descripcion  Policy para autorización de ausencias docentes.
 *              Admin puede ver/editar todas; teacher solo las propias.
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
 * @creado       2026-05-17
 *
 * @modificado   2026-05-17
 *
 * @cambios      2026-05-17 - Creación inicial de la policy
 */

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Auth\SamRole;
use App\Models\SamIdentity;
use App\Models\TeacherAbsence;

class TeacherAbsencePolicy
{
    public function viewAny(SamIdentity $user): bool
    {
        return true;
    }

    public function view(SamIdentity $user, TeacherAbsence $absence): bool
    {
        return $user->role === SamRole::ADMIN
            || $user->external_id === $absence->teacher_external_id;
    }

    public function create(SamIdentity $user): bool
    {
        return true;
    }

    public function update(SamIdentity $user, TeacherAbsence $absence): bool
    {
        return $user->role === SamRole::ADMIN
            || $user->external_id === $absence->teacher_external_id;
    }

    public function delete(SamIdentity $user, TeacherAbsence $absence): bool
    {
        return $user->role === SamRole::ADMIN
            || $user->external_id === $absence->teacher_external_id;
    }
}
