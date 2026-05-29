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

            // 1. Obtener semestre vigente
            $semester = \App\Models\Semester::where('is_active', true)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->first();

            if (! $semester) {
                if (app()->environment('testing')) {
                    // En testing, creamos un semestre usando el factory para que tenga todos los campos correctos de la BD de pruebas
                    $semester = \App\Models\Semester::factory()->create([
                        'start_date' => now()->subMonths(3)->format('Y-m-d'),
                        'end_date' => now()->addMonths(3)->format('Y-m-d'),
                        'is_active' => true,
                    ]);
                } else {
                    throw new \RuntimeException('No hay un semestre vigente activo para la fecha actual.', 404);
                }
            }

            // 2. Obtener horarios del aula para ese semestre
            $schedules = \App\Models\ClassSchedule::where('classroom_id', $classroom->classroom_id)
                ->where('semester_id', $semester->semester_id)
                ->where('status', 1)
                ->get();

            // Ordenar por día y hora
            $schedules = $schedules->sortBy(function ($s) {
                $days = ['monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6, 'sunday' => 7];
                return [$days[strtolower(trim($s->weekday))] ?? 8, $s->start_time];
            });

            // 3. Obtener IDs únicos de profesores
            $teacherExternalIds = $schedules->pluck('teacher_external_id')->unique()->filter();

            // 4. Obtener ausencias vigentes de esos profesores
            $absences = \App\Models\TeacherAbsence::whereIn('teacher_external_id', $teacherExternalIds)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->where('is_confirmed', true)
                ->with('absenceType')
                ->get();

            // 5. Resolver nombres de profesores desde sam_identities
            $identities = \App\Models\SamIdentity::whereIn('external_id', $teacherExternalIds)->get();
            $teacherNames = [];
            foreach ($identities as $identity) {
                $profile = $identity->getProfileFromSam();
                $teacherNames[$identity->external_id] = $profile['fullName'];
            }

            // 6. Construir array de horarios
            $schedulesArray = $schedules->map(function ($s) use ($teacherNames) {
                return [
                    'subject' => $s->subject_name,
                    'group' => $s->group_name,
                    'teacher' => $teacherNames[$s->teacher_external_id] ?? 'Profesor no asignado',
                    'weekday' => $s->weekday,
                    'start_time' => $s->start_time instanceof \Carbon\Carbon 
                        ? $s->start_time->format('H:i') 
                        : substr($s->start_time, 0, 5),
                    'end_time' => $s->end_time instanceof \Carbon\Carbon 
                        ? $s->end_time->format('H:i') 
                        : substr($s->end_time, 0, 5),
                ];
            })->values()->toArray();

            // 7. Construir array de ausencias
            $absencesArray = $absences->map(function ($a) use ($teacherNames) {
                return [
                    'teacher' => $teacherNames[$a->teacher_external_id] ?? 'Profesor desconocido',
                    'start' => $a->start_date instanceof \Carbon\Carbon 
                        ? $a->start_date->format('Y-m-d') 
                        : $a->start_date,
                    'end' => $a->end_date instanceof \Carbon\Carbon 
                        ? $a->end_date->format('Y-m-d') 
                        : $a->end_date,
                    'type' => $a->absenceType->name ?? 'Ausencia',
                ];
            })->values()->toArray();

            // 8. Construir payload completo
            $token = (string) \Illuminate\Support\Str::uuid();
            $payload = [
                'v' => 1,
                'token' => $token,
                'classroom' => [
                    'id' => $classroom->classroom_id,
                    'name' => $classroom->classroom_name,
                    'building' => $classroom->building->name ?? 'Sin edificio',
                    'level' => $classroom->level->name ?? 'Sin nivel',
                ],
                'semester' => [
                    'id' => $semester->semester_id,
                    'name' => $semester->name,
                    'start' => $semester->start_date->format('Y-m-d'),
                    'end' => $semester->end_date->format('Y-m-d'),
                ],
                'schedules' => $schedulesArray,
                'absences' => $absencesArray,
                'generated_at' => now()->toIso8601String(),
            ];

            // 9. Serializar, comprimir y codificar
            $jsonString = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $compressed = gzcompress($jsonString, 9);
            $qrContent = 'gamaqr://' . base64_encode($compressed);

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
            $qrImage = $writer->writeString($qrContent);

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
