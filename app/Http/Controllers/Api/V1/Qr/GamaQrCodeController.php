<?php

/**
 * @descripcion  Controller API para gestión de códigos QR de aulas.
 *
 * @autor        Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @mantenimiento Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @version      1.1.0
 *
 * @creado       2026-05-14
 *
 * @modificado   2026-05-18
 *
 * @cambios      2026-05-18 - Refactorización: eliminar repo injection, agregar authorize en show/download/file, corregir prólogo
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Qr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Qr\DownloadQrRequest;
use App\Http\Requests\Qr\GenerateQrRequest;
use App\Http\Resources\Qr\QrCodeResource;
use App\Jobs\GenerateQrBatchJob;
use App\Models\QrCode;
use App\Services\Qr\GamaQrCodeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GamaQrCodeController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly GamaQrCodeService $service,
    ) {}

    public function generate(int $classroomId, GenerateQrRequest $request): JsonResponse
    {
        $this->authorize('create', QrCode::class);

        try {
            $force = (bool) $request->input('force_regenerate', false);
            $qrCode = $this->service->generateForClassroom($classroomId, $force);

            return $this->success(new QrCodeResource($qrCode), 'QR code generated successfully.', 201);
        } catch (\RuntimeException $e) {
            $statusCode = (int) $e->getCode() ?: 422;

            return $statusCode === 409
                ? $this->error($e->getMessage(), 409)
                : $this->error($e->getMessage(), $statusCode);
        }
    }

    public function show(int $classroomId): JsonResponse
    {
        $qrCode = $this->service->getActiveQr($classroomId);
        if (! $qrCode) {
            return $this->error('No active QR code found for this classroom.', 404);
        }
        $this->authorize('view', $qrCode);

        return $this->success(new QrCodeResource($qrCode), 'QR code retrieved successfully.');
    }

    public function download(DownloadQrRequest $request): JsonResponse
    {
        $this->authorize('viewAny', QrCode::class);

        $classroomIds = $request->input('classroom_ids');
        $format = $request->input('format');
        $batchId = (string) Str::uuid();

        GenerateQrBatchJob::dispatch($classroomIds, $format, $batchId);

        return $this->success(['batchId' => $batchId], 'Download batch queued successfully.');
    }

    public function file(int $id): mixed
    {
        $qrCode = $this->service->findById($id);
        if (! $qrCode || ! $qrCode->file_path || ! Storage::disk('local')->exists($qrCode->file_path)) {
            return $this->error('File not found.', 404);
        }
        $this->authorize('view', $qrCode);

        return Storage::disk('local')->download($qrCode->file_path, "qr-{$qrCode->token}.png");
    }
}
