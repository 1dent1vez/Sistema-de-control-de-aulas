<?php

/**
 * @descripcion  Repositorio de tipos de ausencia que encapsula el acceso a datos de gama_absence_types.
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
 * @cambios      2026-05-13 - Creación inicial del repositorio
 */

declare(strict_types=1);

namespace App\Repositories\Catalogs;

use App\Models\AbsenceType;
use App\Repositories\Contracts\AbsenceTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GamaAbsenceTypeRepository implements AbsenceTypeRepositoryInterface
{
    public function all(): Collection
    {
        return AbsenceType::all();
    }

    public function findById(int $id): ?AbsenceType
    {
        return AbsenceType::find($id);
    }
}
