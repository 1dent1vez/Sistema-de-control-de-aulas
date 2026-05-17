<?php

declare(strict_types=1);

namespace App\Services\Qr;

use App\Models\Classroom;
use App\Models\QrCode;
use App\Repositories\Contracts\QrCodeRepositoryInterface;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GamaQrCodeService
{
    public function __construct(
        private readonly QrCodeRepositoryInterface $repository
    ) {}

    public function generateForClassroom(int $classroomId, bool $force = false): QrCode
    {
        $classroom = Classroom::with('building')->find($classroomId);

        if (! $classroom) {
            throw new \RuntimeException('El aula seleccionada no existe.', 404);
        }

        $active = $this->repository->findActiveByClassroom($classroomId);

        if ($active && ! $force) {
            throw new \RuntimeException('Ya existe un código QR activo para esta aula. Utiliza forceRegenerate para reemplazarlo.', 409);
        }

        if ($active && $force) {
            $this->repository->update($active, [
                'is_active' => false,
                'invalidated_at' => now(),
            ]);
        }

        $token = (string) Str::uuid();
        $payload = [
            'classroomId' => $classroom->id,
            'classroomName' => $classroom->classroom_name,
            'buildingName' => $classroom->building?->name ?? '',
            'token' => $token,
        ];

        $qrDir = 'qr';
        if (! Storage::disk('local')->exists($qrDir)) {
            Storage::disk('local')->makeDirectory($qrDir);
        }

        $fileName = "{$token}.png";
        $filePath = "{$qrDir}/{$fileName}";

        $renderer = new ImageRenderer(
            new RendererStyle(300, 2),
            new SvgImageBackEnd
        );
        $writer = new Writer($renderer);
        $qrImage = $writer->writeString(json_encode($payload));

        Storage::disk('local')->put($filePath, $qrImage);

        return $this->repository->create([
            'classroom_id' => $classroomId,
            'token' => $token,
            'payload' => $payload,
            'file_path' => $filePath,
            'is_active' => true,
            'generated_at' => now(),
        ]);
    }

    public function getActiveQr(int $classroomId): ?QrCode
    {
        return $this->repository->findActiveByClassroom($classroomId);
    }

    public function downloadBatch(array $classroomIds, string $format): string
    {
        $qrCodes = $this->repository->getActiveByClassroomIds($classroomIds);

        if ($qrCodes->isEmpty()) {
            throw new \RuntimeException('No se encontraron códigos QR activos para las aulas especificadas.', 404);
        }

        $batchId = (string) Str::uuid();
        $batchDir = "downloads/{$batchId}";
        Storage::disk('local')->makeDirectory($batchDir);

        if ($format === 'png') {
            if ($qrCodes->count() === 1) {
                $qrCode = $qrCodes->first();
                $destPath = "{$batchDir}/{$qrCode->token}.png";
                Storage::disk('local')->copy($qrCode->file_path, $destPath);

                return $destPath;
            }

            $zipPath = "{$batchDir}/qr-codes.zip";
            $zip = new \ZipArchive;

            if ($zip->open(Storage::disk('local')->path($zipPath), \ZipArchive::CREATE) === true) {
                foreach ($qrCodes as $qrCode) {
                    $filename = "{$qrCode->classroom->classroom_name}.png";
                    $zip->addFile(Storage::disk('local')->path($qrCode->file_path), $filename);
                }
                $zip->close();
            }

            return $zipPath;
        }

        if ($format === 'pdf') {
            $classrooms = $qrCodes->map(fn ($qr) => [
                'classroomName' => $qr->classroom?->classroom_name ?? 'Unknown',
                'buildingName' => $qr->classroom?->building?->name ?? '',
                'qrPath' => Storage::disk('local')->path($qr->file_path),
            ]);

            $pdf = Pdf::loadView('qr-codes-export', ['classrooms' => $classrooms]);
            $pdfPath = "{$batchDir}/qr-codes.pdf";
            Storage::disk('local')->put($pdfPath, $pdf->output());

            return $pdfPath;
        }

        throw new \RuntimeException('Formato inválido. Utiliza pdf o png.', 422);
    }
}
