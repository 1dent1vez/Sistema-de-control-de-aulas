<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Qr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Qr\DownloadQrRequest;
use App\Http\Requests\Qr\GenerateQrRequest;
use App\Http\Resources\Qr\QrCodeResource;
use App\Jobs\GenerateQrBatchJob;
use App\Repositories\Contracts\QrCodeRepositoryInterface;
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
        private readonly QrCodeRepositoryInterface $qrCodeRepository,
    ) {}

    public function generate(int $classroomId, GenerateQrRequest $request): JsonResponse
    {
        try {
            $force = (bool) $request->input('force_regenerate', false);
            $qrCode = $this->service->generateForClassroom($classroomId, $force);

            return $this->success(
                new QrCodeResource($qrCode),
                'QR code generated successfully.',
                201
            );
        } catch (\RuntimeException $e) {
            $statusCode = $e->getCode() ?: 422;

            if ($statusCode === 409) {
                return response()->json([
                    'success' => false,
                    'statusCode' => 409,
                    'message' => $e->getMessage(),
                    'data' => null,
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
            return $this->error('No active QR code found for this classroom.', 404);
        }

        return $this->success(
            new QrCodeResource($qrCode),
            'QR code retrieved successfully.'
        );
    }

    public function download(DownloadQrRequest $request): JsonResponse
    {
        try {
            $classroomIds = $request->input('classroom_ids');
            $format = $request->input('format');

            $batchId = (string) Str::uuid();
            GenerateQrBatchJob::dispatch($classroomIds, $format, $batchId);

            return $this->success(
                ['batchId' => $batchId],
                'Download batch queued successfully.'
            );
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 422);
        }
    }

    public function file(int $id)
    {
        $qrCode = $this->qrCodeRepository->findById($id);

        if (! $qrCode || ! $qrCode->file_path || ! Storage::disk('local')->exists($qrCode->file_path)) {
            return $this->error('File not found.', 404);
        }

        return Storage::disk('local')->download($qrCode->file_path, "qr-{$qrCode->token}.png");
    }
}
