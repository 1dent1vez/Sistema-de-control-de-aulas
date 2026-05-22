<?php

/**
 * @descripcion  Interfaz del repositorio de códigos QR.
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
 * @modificado   2026-05-19
 *
 * @cambios      2026-05-19 - Estandarización de prólogo según formato GAMA
 */

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\QrCode;
use Illuminate\Database\Eloquent\Collection;

interface QrCodeRepositoryInterface
{
    public function findActiveByClassroom(int $classroomId): ?QrCode;

    public function findById(int $id): ?QrCode;

    public function create(array $data): QrCode;

    public function update(QrCode $qrCode, array $data): QrCode;

    public function getActiveByClassroomIds(array $classroomIds): Collection;
}
