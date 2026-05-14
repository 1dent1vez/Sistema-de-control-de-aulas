<?php

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
