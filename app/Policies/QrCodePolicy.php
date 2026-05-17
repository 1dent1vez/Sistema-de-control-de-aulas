<?php

/**
 * @descripcion  Policy para autorización de operaciones sobre códigos QR (admin-only para writes).
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

class QrCodePolicy
{
    public function viewAny(SamIdentity $user): bool
    {
        return true;
    }

    public function view(SamIdentity $user): bool
    {
        return true;
    }

    public function create(SamIdentity $user): bool
    {
        return $user->role === SamRole::ADMIN;
    }

    public function update(SamIdentity $user): bool
    {
        return $user->role === SamRole::ADMIN;
    }

    public function delete(SamIdentity $user): bool
    {
        return $user->role === SamRole::ADMIN;
    }
}
