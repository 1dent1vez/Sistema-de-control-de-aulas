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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

            return $this->success(new QrCodeResource($qrCode), 'Código QR generado exitosamente.', 201);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            $statusCode = (int) $e->getCode() ?: 422;

            if ($statusCode === 409) {
                $activeQr = $this->service->getActiveQr($classroomId);

                return response()->json([
                    'success' => false,
                    'statusCode' => 409,
                    'message' => 'Ya existe un QR activo para esta aula. Use force_regenerate (forceRegenerate) para reemplazarlo.',
                    'data' => [
                        'existingQrId' => $activeQr?->id,
                        'generatedAt' => $activeQr?->generated_at?->toISOString(),
                    ],
                    'errors' => [],
                ], 409);
            }

            return $this->error($e->getMessage(), $statusCode);
        }
    }

    public function show(int $classroomId): JsonResponse
    {
        $qrCode = $this->service->getActiveQr($classroomId);
        if (! $qrCode) {
            return $this->error('No hay un código QR activo para esta aula.', 404);
        }
        $this->authorize('view', $qrCode);

        return $this->success(new QrCodeResource($qrCode), 'Código QR recuperado.');
    }

    public function download(DownloadQrRequest $request): JsonResponse
    {
        $this->authorize('viewAny', QrCode::class);

        $classroomIds = $request->input('classroom_ids');
        $format = $request->input('format');
        $batchId = (string) Str::uuid();

        Cache::put("qr_batch_{$batchId}", [
            'status' => 'pending',
            'progress' => 0,
        ], 600);

        GenerateQrBatchJob::dispatch($classroomIds, $format, $batchId);

        return $this->success(['batchId' => $batchId], 'Descarga por lote encolada exitosamente.');
    }

    public function downloadStatus(string $batchId): JsonResponse
    {
        $this->authorize('viewAny', QrCode::class);

        $status = Cache::get("qr_batch_{$batchId}");
        if (! $status) {
            $status = [
                'status' => 'failed',
                'progress' => 0,
                'error' => 'Batch not found in cache.',
            ];
        }

        return $this->success($status, 'Estado del lote recuperado exitosamente.');
    }

    public function downloadFile(string $batchId): mixed
    {
        $dir = "downloads/{$batchId}";
        try {
            $files = Storage::disk('local')->files($dir);
            if (empty($files)) {
                return response()->json([
                    'message' => 'No se pudo generar el archivo',
                    'error' => 'No files found in batch directory.',
                ], 500);
            }
            $filePath = $files[0];
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);

            return Storage::disk('local')->download($filePath, "qr-codes-batch.{$extension}");
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'No se pudo generar el archivo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function file(int $id, Request $request): mixed
    {
        $qrCode = $this->service->findById($id);
        if (! $qrCode) {
            return $this->error('Archivo no encontrado.', 404);
        }

        $format = $request->query('format', 'png');

        try {
            if ($format === 'pdf') {
                $pdfPath = $this->service->downloadBatch([$qrCode->classroom_id], 'pdf');
                if (Storage::disk('local')->exists($pdfPath)) {
                    return Storage::disk('local')->download($pdfPath, "qr-{$qrCode->token}.pdf");
                }
            } else {
                if ($qrCode->file_path && Storage::disk('local')->exists($qrCode->file_path)) {
                    if ($request->query('download') || $request->query('format') === 'png') {
                        return Storage::disk('local')->download($qrCode->file_path, "qr-{$qrCode->token}.png", [
                            'Content-Type' => 'image/svg+xml',
                        ]);
                    }

                    return Storage::disk('local')->response($qrCode->file_path, null, [
                        'Content-Type' => 'image/svg+xml',
                        'Content-Disposition' => 'inline',
                    ]);
                }
            }

            return $this->error('Archivo no encontrado.', 404);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'No se pudo generar el archivo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
