<?php

/**
 * @descripcion  Service con lógica de negocio para generación, consulta y descarga de QR.
 *
 * @autor        Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @mantenimiento Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @version      1.3.0
 *
 * @creado       2026-05-14
 *
 * @modificado   2026-05-26
 *
 * @cambios      2026-05-18 - Unificación de prólogo; agregado findById
 *               2026-05-26 - Actualización para guardar la URL pública de horario en el código QR generado
 *               2026-05-26 - Corrección: permitir regeneración libre de QR sin forzar parámetro forceRegenerate
 */

declare(strict_types=1);

namespace App\Services\Qr;

use App\Models\QrCode;
use App\Repositories\Contracts\ClassroomRepositoryInterface;
use App\Repositories\Contracts\QrCodeRepositoryInterface;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GamaQrCodeService
{
    public function __construct(
        private readonly QrCodeRepositoryInterface $repository,
        private readonly ClassroomRepositoryInterface $classroomRepository
    ) {}

    /**
     * Genera un código QR para un aula, opcionalmente forzando regeneración.
     *
     *
     * @throws \RuntimeException Si el aula no existe o ya tiene QR activo sin force
     */
    public function generateForClassroom(int $classroomId, bool $force = false): QrCode
    {
        $classroom = $this->classroomRepository->findById($classroomId);

        if (! $classroom) {
            throw new \RuntimeException('El aula seleccionada no existe.', 404);
        }

        if ($classroom->building_id === null || $classroom->classroom_name === null || $classroom->classroom_name === '') {
            throw new \InvalidArgumentException('El aula debe tener nombre y edificio definidos para generar un QR.', 422);
        }

        $active = $this->repository->findActiveByClassroom($classroomId);

        return DB::transaction(function () use ($classroomId, $classroom, $active) {
            if ($active) {
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
            $url = route('qr.aula.horario', ['aula_id' => $classroom->id]);
            $qrImage = $writer->writeString($url);

            Storage::disk('local')->put($filePath, $qrImage);

            return $this->repository->create([
                'classroom_id' => $classroomId,
                'token' => $token,
                'payload' => $payload,
                'file_path' => $filePath,
                'is_active' => true,
                'generated_at' => now(),
            ]);
        });
    }

    /**
     * Obtiene el QR activo de un aula.
     */
    public function getActiveQr(int $classroomId): ?QrCode
    {
        return $this->repository->findActiveByClassroom($classroomId);
    }

    /**
     * Busca un QR por su ID.
     */
    public function findById(int $id): ?QrCode
    {
        return $this->repository->findById($id);
    }

    /**
     * Descarga lote de QRs en ZIP o PDF.
     *
     * @param  array<int, int>  $classroomIds
     * @return string Ruta del archivo generado
     *
     * @throws \RuntimeException Si no hay QRs activos o formato inválido
     */
    public function downloadBatch(array $classroomIds, string $format, ?string $batchId = null): string
    {
        $qrCodes = $this->repository->getActiveByClassroomIds($classroomIds);

        if ($qrCodes->isEmpty()) {
            throw new \RuntimeException('No se encontraron códigos QR activos para las aulas especificadas.', 404);
        }

        $batchId = $batchId ?: (string) Str::uuid();
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
                'classroomName' => $qr->classroom?->classroom_name ?? 'Desconocido',
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
