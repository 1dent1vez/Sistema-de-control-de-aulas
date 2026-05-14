<?php

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
        return QrCode::with('classroom')->find($id);
    }

    public function create(array $data): QrCode
    {
        return QrCode::create($data);
    }

    public function update(QrCode $qrCode, array $data): QrCode
    {
        $qrCode->update($data);

        return $qrCode->fresh()->load('classroom');
    }

    public function getActiveByClassroomIds(array $classroomIds): Collection
    {
        return QrCode::whereIn('classroom_id', $classroomIds)
            ->where('is_active', true)
            ->with('classroom')
            ->get();
    }
}
