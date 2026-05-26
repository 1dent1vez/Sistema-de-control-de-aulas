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
 * @version      1.4.0
 *
 * @creado       2026-05-13
 *
 * @modificado   2026-05-26
 *
 * @cambios      2026-05-13 - Creación inicial del servicio
 *               2026-05-25 - Implementación de columnas en español, validación de semestre vigente, solapamientos, parsing de días, y lookup de docentes en SAM.
 *               2026-05-26 - Optimización crítica de performance: pre-carga de aulas, docentes y horarios en memoria, y caché de búsquedas para eliminar N+1 consultas.
 *               2026-05-26 - Corrección de UNIQUE constraint al importar docentes duplicados y estandarización de reportes de errores de base de datos.
 *               2026-05-26 - Separación del flujo de importación en Preview (sólo lectura) y Confirmación (persistencia atómica) con resolves en JSON.
 */

declare(strict_types=1);

namespace App\Services\Schedules;

use App\Enums\Auth\SamRole;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\SamEmployee;
use App\Models\SamIdentity;
use App\Models\Semester;
use App\Repositories\Contracts\ClassroomRepositoryInterface;
use App\Repositories\Contracts\ClassScheduleRepositoryInterface;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GamaScheduleImportService
{
    private const CHUNK_SIZE = 50;

    public function __construct(
        private readonly ClassroomRepositoryInterface $classroomRepository,
        private readonly ClassScheduleRepositoryInterface $scheduleRepository
    ) {}

    private const REQUIRED_COLUMNS = [
        'aula', 'docente', 'materia', 'grupo', 'dias', 'hora_inicio', 'hora_fin',
    ];

    /**
     * Importa horarios desde un archivo CSV/XLSX en chunks transaccionales.
     *
     * @param  UploadedFile|string  $file  Archivo o ruta absoluta en disco
     * @param  int  $semesterId  ID del semestre al que asignar los horarios
     * @param  string  $batchId  ID único del lote de importación
     * @return array{imported: int, errors: array<int, array{row: int, error: string}>, report_path: string|null}
     */
    public function import(UploadedFile|string $file, int $semesterId, string $batchId, bool $isConfirm = true): array
    {
        $imported = 0;
        $rows = [];

        // Validación RF-05.3 — Semestre vigente
        try {
            $today = now()->format('Y-m-d');
            $semestreVigente = Semester::vigente($today)->first();
            if (! $semestreVigente) {
                $report = [['row' => 0, 'error' => 'No existe semestre vigente. No se puede registrar horarios hasta que se cree un semestre activo.']];
                $reportPath = "imports/{$batchId}.json";
                Storage::disk('local')->put($reportPath, json_encode($report, JSON_PRETTY_PRINT));

                return [
                    'imported' => 0,
                    'errors' => $report,
                    'report_path' => $reportPath,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error de BD al determinar el semestre vigente: '.$e->getMessage());
            $report = [['row' => 0, 'error' => 'Error al determinar el semestre vigente. Intente más tarde.']];
            $reportPath = "imports/{$batchId}.json";
            Storage::disk('local')->put($reportPath, json_encode($report, JSON_PRETTY_PRINT));

            return [
                'imported' => 0,
                'errors' => $report,
                'report_path' => $reportPath,
            ];
        }

        try {
            // Resolver ruta absoluta física en el disco
            $absolutePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

            $spreadsheet = IOFactory::load($absolutePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rawRows = $worksheet->toArray();

            // Filtrar filas completamente vacías al final del archivo para optimizar rendimiento
            $rows = array_values(array_filter($rawRows, function ($row) {
                return ! empty($row) && ! empty(array_filter($row, fn ($cell) => $cell !== null && trim((string) $cell) !== ''));
            }));
        } catch (\Exception $e) {
            Log::error('Error leyendo archivo de horarios: '.$e->getMessage());
            $report = [['row' => 0, 'error' => 'Archivo dañado, vuelva a cargar el archivo']];
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

        $header = array_map(fn ($h) => mb_strtolower(trim((string) $h)), $rows[0]);
        $headerIndex = array_flip($header);

        $missing = array_diff(self::REQUIRED_COLUMNS, $header);
        $extra = array_diff($header, self::REQUIRED_COLUMNS);

        if (! empty($missing) || ! empty($extra)) {
            $expectedStr = implode(', ', self::REQUIRED_COLUMNS);
            $foundStr = implode(', ', $header);
            $errorMsg = "Columnas incorrectas. Esperadas: [{$expectedStr}]. Encontradas: [{$foundStr}].";
            if (! empty($missing)) {
                $errorMsg .= ' Faltantes: '.implode(', ', $missing).'.';
            }
            if (! empty($extra)) {
                $errorMsg .= ' Extra: '.implode(', ', $extra).'.';
            }

            $report = [['row' => 1, 'error' => $errorMsg]];
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

        // --- PRE-CARGA CRÍTICA PARA ELIMINAR CONSULTAS N+1 ---
        try {
            // 1. Aulas en memoria
            $classroomsByName = Classroom::all()->keyBy('classroom_name');
            $classroomsById = Classroom::all()->keyBy('id');

            // 2. Docentes locales en memoria
            $samIdentities = SamIdentity::all();
            $teachersByExternalId = [];
            $teachersByEmail = [];
            $teachersByExactFullName = [];

            foreach ($samIdentities as $si) {
                $teachersByExternalId[$si->external_id] = $si;
                if ($si->email) {
                    $teachersByEmail[mb_strtolower($si->email)] = $si;
                }
                if ($si->full_name) {
                    $teachersByExactFullName[mb_strtolower($si->full_name)] = $si;
                }
            }

            // Cache de búsquedas local en memoria
            $teacherLookupCache = [];

            // 3. Horarios existentes en memoria
            $existingSchedules = ClassSchedule::where('semester_id', $semesterId)->get();
            $schedulesByClassroom = [];
            $schedulesByTeacher = [];

            foreach ($existingSchedules as $sch) {
                $startTimeShort = substr($sch->start_time, 0, 5);
                $endTimeShort = substr($sch->end_time, 0, 5);
                $schedulesByClassroom[$sch->classroom_id][$sch->weekday][] = [
                    'start' => $startTimeShort,
                    'end' => $endTimeShort,
                ];
                $schedulesByTeacher[$sch->teacher_external_id][$sch->weekday][] = [
                    'start' => $startTimeShort,
                    'end' => $endTimeShort,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error de BD al pre-cargar datos para importación: '.$e->getMessage());
            $report = [['row' => 0, 'error' => 'Error al preparar la base de datos para procesar la importación.']];
            $reportPath = "imports/{$batchId}.json";
            Storage::disk('local')->put($reportPath, json_encode($report, JSON_PRETTY_PRINT));

            return [
                'imported' => 0,
                'errors' => $report,
                'report_path' => $reportPath,
            ];
        }

        $now = now();

        try {
            foreach ($chunks as $chunkIndex => $chunk) {
                DB::transaction(function () use (
                    $chunk,
                    $headerIndex,
                    $semesterId,
                    &$imported,
                    &$report,
                    $chunkIndex,
                    $now,
                    $classroomsByName,
                    $classroomsById,
                    &$teachersByExternalId,
                    &$teachersByEmail,
                    &$teachersByExactFullName,
                    &$samIdentities,
                    &$teacherLookupCache,
                    &$schedulesByClassroom,
                    &$schedulesByTeacher,
                    $isConfirm
                ) {
                    $toInsert = [];
                    $rowOffset = $chunkIndex * self::CHUNK_SIZE + 2;

                    foreach ($chunk as $rowIndex => $row) {
                        $rowNum = $rowOffset + $rowIndex;

                        // Si la fila está completamente vacía, la omitimos
                        if (empty(array_filter($row))) {
                            continue;
                        }

                        $rowData = array_combine(array_keys($headerIndex), $row);
                        $rowErrors = [];

                        $aulaInput = trim((string) ($rowData['aula'] ?? ''));
                        $docenteInput = trim((string) ($rowData['docente'] ?? ''));
                        $materiaInput = trim((string) ($rowData['materia'] ?? ''));
                        $grupoInput = trim((string) ($rowData['grupo'] ?? ''));
                        $diasInput = trim((string) ($rowData['dias'] ?? ''));
                        $startTimeInput = trim((string) ($rowData['hora_inicio'] ?? ''));
                        $endTimeInput = trim((string) ($rowData['hora_fin'] ?? ''));

                        // 1. Aula existente (Búsqueda en memoria)
                        $classroom = null;
                        if (empty($aulaInput)) {
                            $rowErrors[] = 'Aula es obligatoria.';
                        } else {
                            $classroom = $classroomsByName[$aulaInput] ?? null;
                            if (! $classroom && is_numeric($aulaInput)) {
                                $classroom = $classroomsById[(int) $aulaInput] ?? null;
                            }
                            if (! $classroom) {
                                $rowErrors[] = "Aula '{$aulaInput}' no encontrada.";
                            }
                        }

                        // 2. Docente existente (Búsqueda en memoria + SAM)
                        $teacher = null;
                        if (empty($docenteInput)) {
                            $rowErrors[] = 'Docente es obligatorio.';
                        } else {
                            if (array_key_exists($docenteInput, $teacherLookupCache)) {
                                $teacher = $teacherLookupCache[$docenteInput];
                            } else {
                                $teacher = $teachersByExternalId[$docenteInput] ?? null;
                                if (! $teacher) {
                                    $teacher = $teachersByEmail[mb_strtolower($docenteInput)] ?? null;
                                }
                                if (! $teacher) {
                                    $teacher = $teachersByExactFullName[mb_strtolower($docenteInput)] ?? null;
                                }
                                if (! $teacher) {
                                    $normalizedInput = mb_strtolower($docenteInput);
                                    foreach ($samIdentities as $si) {
                                        if ($si->full_name && mb_strpos(mb_strtolower($si->full_name), $normalizedInput) !== false) {
                                            $teacher = $si;
                                            break;
                                        }
                                    }
                                }

                                // Si no se encuentra localmente, buscar en SAM
                                if (! $teacher) {
                                    $employee = SamEmployee::find($docenteInput);
                                    if (! $employee) {
                                        $employee = SamEmployee::where('correo', $docenteInput)
                                            ->orWhere(DB::raw("CONCAT(nombre, ' ', apellidoPa, ' ', apellidoMa)"), 'like', '%'.$docenteInput.'%')
                                            ->first();
                                    }

                                    if ($employee) {
                                        $teacher = SamIdentity::firstOrCreate(
                                            ['external_id' => $employee->id_empleado],
                                            ['role' => SamRole::TEACHER]
                                        );

                                        $teacher->email = $employee->correo ?? ($employee->id_empleado.'@toluca.tecnm.mx');
                                        $teacher->full_name = trim(($employee->nombre ?? '').' '.($employee->apellidoPa ?? '').' '.($employee->apellidoMa ?? ''));
                                        $teacher->save();

                                        // Registrar en colecciones locales en memoria
                                        $teachersByExternalId[$teacher->external_id] = $teacher;
                                        if ($teacher->email) {
                                            $teachersByEmail[mb_strtolower($teacher->email)] = $teacher;
                                        }
                                        if ($teacher->full_name) {
                                            $teachersByExactFullName[mb_strtolower($teacher->full_name)] = $teacher;
                                        }
                                        $samIdentities->push($teacher);
                                    }
                                }

                                $teacherLookupCache[$docenteInput] = $teacher;
                            }

                            if (! $teacher) {
                                $rowErrors[] = "Docente '{$docenteInput}' no encontrado.";
                            }
                        }

                        // 3. Materia obligatoria y válida
                        if (empty($materiaInput)) {
                            $rowErrors[] = 'Materia es obligatoria.';
                        } elseif (mb_strlen($materiaInput) > 100) {
                            $rowErrors[] = 'El nombre de la materia no debe exceder 100 caracteres.';
                        }

                        // 4. Grupo obligatorio y válido
                        if (empty($grupoInput)) {
                            $rowErrors[] = 'Grupo es obligatorio.';
                        } elseif (mb_strlen($grupoInput) > 10) {
                            $rowErrors[] = 'El código del grupo no debe exceder 10 caracteres.';
                        }

                        // 5. Horas válidas
                        $startTimeShort = '';
                        $endTimeShort = '';
                        if (empty($startTimeInput) || empty($endTimeInput)) {
                            $rowErrors[] = 'Hora de inicio y hora de fin son obligatorias.';
                        } elseif (! preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $startTimeInput) || ! preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $endTimeInput)) {
                            $rowErrors[] = 'hora_inicio y hora_fin deben estar en formato HH:MM.';
                        } else {
                            $startTimeShort = substr($startTimeInput, 0, 5);
                            $endTimeShort = substr($endTimeInput, 0, 5);
                            if ($startTimeShort >= $endTimeShort) {
                                $rowErrors[] = 'hora_inicio debe ser menor que hora_fin.';
                            }
                        }

                        // 6. Días válidos
                        $parsedDays = [];
                        if (empty($diasInput)) {
                            $rowErrors[] = 'Días de la semana son obligatorios.';
                        } else {
                            $parsedDays = $this->parseDays($diasInput);
                            if (isset($parsedDays['invalid'])) {
                                $rowErrors[] = "Día inválido: '".implode(', ', $parsedDays['invalid'])."'.";
                            } elseif (empty($parsedDays)) {
                                $rowErrors[] = 'El campo dias no contiene días válidos.';
                            }
                        }

                        // 7. Validaciones de solapamiento en memoria (si no hay errores previos)
                        if (empty($rowErrors) && $classroom && $teacher) {
                            foreach ($parsedDays as $day) {
                                $dayLabel = $this->getDayLabel($day);

                                // Solapamiento en aula en memoria
                                $classroomOverlap = false;
                                if (isset($schedulesByClassroom[$classroom->id][$day])) {
                                    foreach ($schedulesByClassroom[$classroom->id][$day] as $existing) {
                                        if ($startTimeShort < $existing['end'] && $endTimeShort > $existing['start']) {
                                            $classroomOverlap = true;
                                            break;
                                        }
                                    }
                                }

                                if ($classroomOverlap) {
                                    $rowErrors[] = "Solapamiento de horario en aula '{$classroom->classroom_name}' el día '{$dayLabel}'.";
                                }

                                // Solapamiento del docente en memoria
                                $teacherOverlap = false;
                                if (isset($schedulesByTeacher[$teacher->external_id][$day])) {
                                    foreach ($schedulesByTeacher[$teacher->external_id][$day] as $existing) {
                                        if ($startTimeShort < $existing['end'] && $endTimeShort > $existing['start']) {
                                            $teacherOverlap = true;
                                            break;
                                        }
                                    }
                                }

                                if ($teacherOverlap) {
                                    $rowErrors[] = "El docente '".($teacher->full_name ?? $teacher->external_id)."' ya tiene horario asignado el día '{$dayLabel}' a esa hora.";
                                }
                            }
                        }

                        if (! empty($rowErrors)) {
                            $report[] = [
                                'row' => $rowNum,
                                'ok' => false,
                                'status' => 'discarded',
                                'classroomName' => $aulaInput ?: '-',
                                'teacherExternalId' => $docenteInput ?: '-',
                                'subjectName' => $materiaInput ?: '-',
                                'weekday' => $diasInput ?: '-',
                                'startTime' => $startTimeInput ?: '-',
                                'endTime' => $endTimeInput ?: '-',
                                'error' => implode('; ', $rowErrors),
                            ];

                            continue;
                        }

                        // Insertar registros independientes por día
                        foreach ($parsedDays as $day) {
                            $toInsert[] = [
                                'semester_id' => $semesterId,
                                'classroom_id' => $classroom->id,
                                'teacher_external_id' => $teacher->external_id,
                                'subject_name' => $materiaInput,
                                'group_name' => $grupoInput,
                                'weekday' => $day,
                                'start_time' => $startTimeShort,
                                'end_time' => $endTimeShort,
                                'status' => true,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];

                            // Añadir dinámicamente a la estructura de solapamiento en memoria
                            $schedulesByClassroom[$classroom->id][$day][] = [
                                'start' => $startTimeShort,
                                'end' => $endTimeShort,
                            ];
                            $schedulesByTeacher[$teacher->external_id][$day][] = [
                                'start' => $startTimeShort,
                                'end' => $endTimeShort,
                            ];
                        }

                        $report[] = [
                            'row' => $rowNum,
                            'ok' => true,
                            'status' => 'imported',
                            'classroomName' => $classroom->classroom_name,
                            'teacherExternalId' => $teacher->full_name ?? $teacher->external_id,
                            'subjectName' => $materiaInput,
                            'weekday' => $diasInput,
                            'startTime' => $startTimeShort,
                            'endTime' => $endTimeShort,
                            'error' => null,
                            'resolved' => [
                                'semester_id' => $semesterId,
                                'classroom_id' => $classroom->id,
                                'teacher_external_id' => $teacher->external_id,
                                'subject_name' => $materiaInput,
                                'group_name' => $grupoInput,
                                'weekday' => $parsedDays,
                                'start_time' => $startTimeShort,
                                'end_time' => $endTimeShort,
                            ],
                        ];
                    }

                    if (! empty($toInsert)) {
                        if ($isConfirm) {
                            $this->scheduleRepository->insertMultiple($toInsert);
                        }
                        $imported += count($toInsert);
                    }
                });
            }
        } catch (QueryException $e) {
            Log::error('Error de base de datos en importación masiva: '.$e->getMessage(), [
                'batch_id' => $batchId,
                'sql' => $e->getSql(),
            ]);

            $msg = $e->getMessage();
            $friendlyMsg = 'Error en base de datos. Intente nuevamente o contacte al administrador.';
            if (str_contains($msg, 'UNIQUE constraint failed') || str_contains($msg, 'Duplicate entry') || $e->getCode() === '23000') {
                $friendlyMsg = 'El registro que intenta crear ya existe en el sistema.';
            }

            $report = [['row' => 0, 'error' => $friendlyMsg]];
            $reportPath = "imports/{$batchId}.json";
            Storage::disk('local')->put($reportPath, json_encode($report, JSON_PRETTY_PRINT));

            return [
                'imported' => 0,
                'errors' => $report,
                'report_path' => $reportPath,
            ];
        } catch (\PDOException $e) {
            Log::error('Error de conexión de BD en importación masiva: '.$e->getMessage());

            $report = [['row' => 0, 'error' => 'Error de conexión con la base de datos. Contacte al administrador.']];
            $reportPath = "imports/{$batchId}.json";
            Storage::disk('local')->put($reportPath, json_encode($report, JSON_PRETTY_PRINT));

            return [
                'imported' => 0,
                'errors' => $report,
                'report_path' => $reportPath,
            ];
        } catch (\Exception $e) {
            Log::error('Error inesperado en importación masiva: '.$e->getMessage());

            $report = [['row' => 0, 'error' => 'Error al procesar la importación. Intente nuevamente.']];
            $reportPath = "imports/{$batchId}.json";
            Storage::disk('local')->put($reportPath, json_encode($report, JSON_PRETTY_PRINT));

            return [
                'imported' => 0,
                'errors' => $report,
                'report_path' => $reportPath,
            ];
        }

        $reportPath = "imports/{$batchId}.json";
        Storage::disk('local')->put($reportPath, json_encode($report, JSON_PRETTY_PRINT));

        return [
            'imported' => $imported,
            'errors' => $report,
            'report_path' => $reportPath,
        ];
    }

    /**
     * Confirma e inserta los horarios pre-validados del lote (batch) en una transacción.
     *
     * @param  string  $batchId  ID único del lote de importación
     * @return array{success: bool, imported: int}
     */
    public function confirm(string $batchId): array
    {
        $reportPath = "imports/{$batchId}.json";
        if (! Storage::disk('local')->exists($reportPath)) {
            throw new \RuntimeException('El reporte de importación no existe o ha expirado.');
        }

        $report = json_decode(Storage::disk('local')->get($reportPath), true);
        $toInsert = [];
        $now = now();

        foreach ($report as $r) {
            if (isset($r['ok']) && $r['ok'] && isset($r['resolved'])) {
                $resolved = $r['resolved'];
                foreach ($resolved['weekday'] as $day) {
                    $toInsert[] = [
                        'semester_id' => $resolved['semester_id'],
                        'classroom_id' => $resolved['classroom_id'],
                        'teacher_external_id' => $resolved['teacher_external_id'],
                        'subject_name' => $resolved['subject_name'],
                        'group_name' => $resolved['group_name'],
                        'weekday' => $day,
                        'start_time' => $resolved['start_time'],
                        'end_time' => $resolved['end_time'],
                        'status' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        $imported = 0;
        if (! empty($toInsert)) {
            DB::transaction(function () use ($toInsert, &$imported) {
                $this->scheduleRepository->insertMultiple($toInsert);
                $imported = count($toInsert);
            });
        }

        return [
            'success' => true,
            'imported' => $imported,
        ];
    }

    /**
     * Parsea la cadena de días en español y retorna un array con los días en inglés
     * o un array con la clave 'invalid' si hay días no válidos.
     */
    private function parseDays(string $daysStr): array
    {
        $daysStr = trim($daysStr);
        if (empty($daysStr)) {
            return [];
        }

        $map = [
            'lunes' => 'monday',
            'martes' => 'tuesday',
            'miercoles' => 'wednesday',
            'miércoles' => 'wednesday',
            'jueves' => 'thursday',
            'viernes' => 'friday',
            'sabado' => 'saturday',
            'sábado' => 'saturday',
            'domingo' => 'sunday',
        ];

        $weekDaysOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        // Si es un rango (ej: "Lunes a Viernes", "Lunes al Sábado")
        if (preg_match('/^\s*([a-zA-ZáéíóúÁÉÍÓÚñÑ]+)\s+a[l]?\s+([a-zA-ZáéíóúÁÉÍÓÚñÑ]+)\s*$/u', $daysStr, $matches)) {
            $startDayNorm = mb_strtolower(trim($matches[1]));
            $endDayNorm = mb_strtolower(trim($matches[2]));

            $startEng = $map[$startDayNorm] ?? null;
            $endEng = $map[$endDayNorm] ?? null;

            if (! $startEng || ! $endEng) {
                $invalid = [];
                if (! $startEng) {
                    $invalid[] = $matches[1];
                }
                if (! $endEng) {
                    $invalid[] = $matches[2];
                }

                return ['invalid' => $invalid];
            }

            $startIndex = array_search($startEng, $weekDaysOrder);
            $endIndex = array_search($endEng, $weekDaysOrder);

            if ($startIndex === false || $endIndex === false) {
                return ['invalid' => [$matches[1], $matches[2]]];
            }

            $result = [];
            if ($startIndex <= $endIndex) {
                for ($i = $startIndex; $i <= $endIndex; $i++) {
                    $result[] = $weekDaysOrder[$i];
                }
            } else {
                return ['invalid' => [$daysStr]];
            }

            return $result;
        }

        // Si es una lista separada por comas
        $parts = explode(',', $daysStr);
        $result = [];
        $invalid = [];
        foreach ($parts as $part) {
            $norm = mb_strtolower(trim($part));
            if (empty($norm)) {
                continue;
            }
            if (isset($map[$norm])) {
                $result[] = $map[$norm];
            } else {
                $invalid[] = $part;
            }
        }

        if (! empty($invalid)) {
            return ['invalid' => $invalid];
        }

        return array_unique($result);
    }

    /**
     * Retorna la etiqueta en español para un día en inglés.
     */
    private function getDayLabel(string $day): string
    {
        $map = [
            'monday' => 'Lunes',
            'tuesday' => 'Martes',
            'wednesday' => 'Miércoles',
            'thursday' => 'Jueves',
            'friday' => 'Viernes',
            'saturday' => 'Sábado',
            'sunday' => 'Domingo',
        ];

        return $map[$day] ?? $day;
    }
}
