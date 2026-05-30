<?php

/**
 * @descripcion  Repositorio de códigos QR que encapsula el acceso a datos de gama_qr_codes.
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

namespace App\Repositories\Qr;

use App\Models\QrCode;
use App\Repositories\Contracts\QrCodeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GamaQrCodeRepository implements QrCodeRepositoryInterface
{
    public function findActiveByClassroom(int $classroomId): ?QrCode
    {
        return QrCode::where('classroom_id', $classroomId)
            ->where('is_active', true)
            ->first();
    }

    public function findById(int $id): ?QrCode
    {
        return QrCode::with(['classroom.building', 'classroom.level'])->find($id);
    }

    public function create(array $data): QrCode
    {
        return QrCode::create($data);
    }

    public function update(QrCode $qrCode, array $data): QrCode
    {
        $qrCode->update($data);

        return $qrCode->fresh()->load(['classroom.building', 'classroom.level']);
    }

    public function getActiveByClassroomIds(array $classroomIds): Collection
    {
        return QrCode::whereIn('classroom_id', $classroomIds)
            ->where('is_active', true)
            ->with(['classroom.building', 'classroom.level'])
            ->get();
    }
}
