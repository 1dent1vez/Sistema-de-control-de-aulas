<?php

/**
 * @descripcion  Servicio de importación masiva de horarios desde CSV/XLSX.
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
 * @creado       2026-05-13
 *
 * @modificado   2026-05-13
 *
 * @cambios      2026-05-13 - Creación inicial del servicio
 */

declare(strict_types=1);

namespace App\Services\Schedules;

use App\Enums\Schedules\Weekday;
use App\Repositories\Contracts\ClassroomRepositoryInterface;
use App\Repositories\Contracts\ClassScheduleRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Facades\Excel;

class GamaScheduleImportService
{
    private const CHUNK_SIZE = 50;

    public function __construct(
        private readonly ClassroomRepositoryInterface $classroomRepository,
        private readonly ClassScheduleRepositoryInterface $scheduleRepository
    ) {}

    private const REQUIRED_COLUMNS = [
        'classroom_id', 'teacher_external_id', 'subject_name',
        'group_name', 'weekday', 'start_time', 'end_time',
    ];

    /**
     * Importa horarios desde un archivo CSV/XLSX en chunks transaccionales.
     *
     * @param  int  $semesterId  ID del semestre al que asignar los horarios
     * @param  string  $batchId  ID único del lote de importación
     * @return array{imported: int, errors: array<int, array{row: int, error: string}>, report_path: string|null}
     */
    public function import(UploadedFile $file, int $semesterId, string $batchId): array
    {
        $imported = 0;
        $rows = [];

        try {
            $rows = Excel::toArray(new class implements ToArray
            {
                public function array(array $array): array
                {
                    return $array;
                }
            }, $file)[0] ?? [];
        } catch (\Exception $e) {
            Log::error('Error leyendo archivo de horarios: '.$e->getMessage());
            $report = [['row' => 0, 'error' => 'Archivo dañado o formato no soportado. Vuelva a cargar el archivo.']];
            $reportPath = "imports/{$batchId}.json";
            Storage::disk('local')->put($reportPath, json_encode($report, JSON_PRETTY_PRINT));

            return [
                'imported' => 0,
                'errors' => $report,
                'report_path' => $reportPath,
            ];
        }

        if (empty($rows)) {
            $report = [['row' => 0, 'error' => 'El archivo está vacío o no tiene datos.']];
            $reportPath = "imports/{$batchId}.json";
            Storage::disk('local')->put($reportPath, json_encode($report, JSON_PRETTY_PRINT));

            return [
                'imported' => 0,
                'errors' => $report,
                'report_path' => $reportPath,
            ];
        }

        $header = array_map('trim', $rows[0]);
        $headerIndex = array_flip($header);

        $missing = array_diff(self::REQUIRED_COLUMNS, $header);
        if (! empty($missing)) {
            $report = [['row' => 1, 'error' => 'Columnas faltantes: '.implode(', ', $missing)]];
            $reportPath = "imports/{$batchId}.json";
            Storage::disk('local')->put($reportPath, json_encode($report, JSON_PRETTY_PRINT));

            return [
                'imported' => 0,
                'errors' => $report,
                'report_path' => $reportPath,
            ];
        }

        $dataRows = array_slice($rows, 1);
        $chunks = array_chunk($dataRows, self::CHUNK_SIZE);
        $report = [];

        foreach ($chunks as $chunkIndex => $chunk) {
            DB::transaction(function () use ($chunk, $headerIndex, $semesterId, &$imported, &$report, $chunkIndex) {
                $toInsert = [];
                $rowOffset = $chunkIndex * self::CHUNK_SIZE + 2;

                foreach ($chunk as $rowIndex => $row) {
                    $rowNum = $rowOffset + $rowIndex;
                    $rowData = array_combine(array_keys($headerIndex), $row);
                    $rowErrors = [];

                    $classroomId = (int) ($rowData['classroom_id'] ?? 0);
                    $weekday = mb_strtolower(trim((string) ($rowData['weekday'] ?? '')));
                    $startTime = trim((string) ($rowData['start_time'] ?? ''));
                    $endTime = trim((string) ($rowData['end_time'] ?? ''));
                    $teacherExternalId = trim((string) ($rowData['teacher_external_id'] ?? ''));
                    $subjectName = trim((string) ($rowData['subject_name'] ?? ''));
                    $groupName = trim((string) ($rowData['group_name'] ?? ''));

                    $classroom = null;
                    if (! $classroomId || ! ($classroom = $this->classroomRepository->findById($classroomId))) {
                        $rowErrors[] = "classroom_id '$classroomId' no existe.";
                    }

                    if (! in_array($weekday, Weekday::values(), true)) {
                        $rowErrors[] = "weekday '$weekday' no válido. Use: ".implode(', ', Weekday::values());
                    }

                    if (! preg_match('/^\d{2}:\d{2}$/', $startTime) || ! preg_match('/^\d{2}:\d{2}$/', $endTime)) {
                        $rowErrors[] = 'start_time y end_time deben estar en formato HH:MM.';
                    } elseif ($startTime >= $endTime) {
                        $rowErrors[] = 'start_time debe ser anterior a end_time.';
                    }

                    if (empty($teacherExternalId)) {
                        $rowErrors[] = 'teacher_external_id es obligatorio.';
                    }

                    if (empty($subjectName)) {
                        $rowErrors[] = 'subject_name es obligatorio.';
                    }

                    if (empty($groupName)) {
                        $rowErrors[] = 'group_name es obligatorio.';
                    }

                    if (! empty($rowErrors)) {
                        $report[] = [
                            'row' => $rowNum,
                            'ok' => false,
                            'status' => 'discarded',
                            'classroomName' => $rowData['classroom_id'] ?? '-',
                            'teacherExternalId' => $teacherExternalId ?: '-',
                            'subjectName' => $subjectName ?: '-',
                            'weekday' => $weekday ?: '-',
                            'startTime' => $startTime ?: '-',
                            'endTime' => $endTime ?: '-',
                            'error' => implode('; ', $rowErrors),
                        ];

                        continue;
                    }

                    $toInsert[] = [
                        'semester_id' => $semesterId,
                        'classroom_id' => $classroomId,
                        'teacher_external_id' => $teacherExternalId,
                        'subject_name' => $subjectName,
                        'group_name' => $groupName,
                        'weekday' => $weekday,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'status' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $report[] = [
                        'row' => $rowNum,
                        'ok' => true,
                        'status' => 'imported',
                        'classroomName' => $classroom ? $classroom->name : $classroomId,
                        'teacherExternalId' => $teacherExternalId,
                        'subjectName' => $subjectName,
                        'weekday' => $weekday,
                        'startTime' => $startTime,
                        'endTime' => $endTime,
                        'error' => null,
                    ];
                }

                if (! empty($toInsert)) {
                    $this->scheduleRepository->insertMultiple($toInsert);
                    $imported += count($toInsert);
                }
            });
        }

        $reportPath = "imports/{$batchId}.json";
        Storage::disk('local')->put($reportPath, json_encode($report, JSON_PRETTY_PRINT));

        return [
            'imported' => $imported,
            'errors' => $report,
            'report_path' => $reportPath,
        ];
    }
}
