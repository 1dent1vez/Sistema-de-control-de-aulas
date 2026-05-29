<?php

/**
 * @descripcion  Servicio de ausencias de docentes con validación de traslapes.
 *
 * @autor        Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @mantenimiento Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @version      1.2.0
 *
 * @creado       2026-05-14
 *
 * @modificado   2026-05-26
 *
 * @cambios      2026-05-14 - Creación inicial del servicio
 *               2026-05-25 - Implementación de RF-08: asociación automática a class_schedules, validaciones de rango y estadísticas.
 *               2026-05-26 - Estandarización y traducción de excepciones y mensajes de error en español.
 */

declare(strict_types=1);

namespace App\Services\TeacherStatus;

use App\Events\TeacherAbsenceRegistered;
use App\Exceptions\NoClassesInPeriodException;
use App\Models\ClassSchedule;
use App\Models\SamIdentity;
use App\Models\Semester;
use App\Models\TeacherAbsence;
use App\Repositories\Contracts\TeacherAbsenceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GamaTeacherAbsenceService
{
    public function __construct(
        private readonly TeacherAbsenceRepositoryInterface $repository
    ) {}

    /**
     * Obtiene todas las ausencias, opcionalmente filtradas.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, TeacherAbsence>
     */
    public function getAll(array $filters = [], ?SamIdentity $user = null): Collection
    {
        if ($user !== null && ! $user->isAdmin()) {
            $filters['teacher_external_id'] = $user->external_id;
        }

        return $this->repository->all($filters);
    }

    /**
     * Busca una ausencia por su ID.
     */
    public function getById(int $id): ?TeacherAbsence
    {
        return $this->repository->findById($id);
    }

    /**
     * Verifica si hay traslapes de ausencias para un docente en un rango.
     *
     * @return Collection<int, TeacherAbsence>
     */
    public function checkOverlap(string $teacherExternalId, string $startDate, string $endDate, ?int $excludeId = null): Collection
    {
        return $this->repository->findOverlappingAbsences($teacherExternalId, $startDate, $endDate, $excludeId);
    }

    /**
     * Registra una nueva ausencia con validación de traslape y asociación de clases.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws OverlapRequiredException Si hay traslape y no está confirmado
     * @throws NoClassesInPeriodException Si el docente no tiene clases en el período
     * @throws \RuntimeException Si la ausencia está en el pasado
     */
    public function store(array $data): TeacherAbsence
    {
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        $teacherExternalId = $data['teacher_external_id'];

        if ($endDate < now()->format('Y-m-d')) {
            throw new \RuntimeException('La fecha de inicio no puede ser anterior a la fecha actual.');
        }

        // Buscar clases afectadas en el rango
        $affectedSchedules = $this->getAffectedSchedules($teacherExternalId, $startDate, $endDate);
        if ($affectedSchedules->isEmpty()) {
            throw new NoClassesInPeriodException;
        }

        $overlaps = $this->repository->findOverlappingAbsences($teacherExternalId, $startDate, $endDate);

        if ($overlaps->isNotEmpty() && empty($data['is_confirmed'])) {
            throw new OverlapRequiredException(
                'El docente ya tiene una ausencia registrada en ese periodo.',
                $overlaps
            );
        }

        if (! isset($data['is_confirmed'])) {
            $data['is_confirmed'] = false;
        }

        return DB::transaction(function () use ($data, $affectedSchedules) {
            $absence = $this->repository->create($data);
            $absence->classSchedules()->sync($affectedSchedules->pluck('class_schedule_id')->toArray());

            event(new TeacherAbsenceRegistered($absence));

            return $absence->fresh()->load('classSchedules.classroom', 'absenceType');
        });
    }

    /**
     * Actualiza una ausencia existente con validación de traslape y re-asociación de clases.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws OverlapRequiredException Si hay traslape y no está confirmado
     * @throws NoClassesInPeriodException Si el docente no tiene clases en el período
     * @throws \RuntimeException Si la ausencia ya inició
     */
    public function update(int $id, array $data): ?TeacherAbsence
    {
        $absence = $this->repository->findById($id);

        if (! $absence) {
            return null;
        }

        $startDate = $data['start_date'] ?? $absence->start_date->format('Y-m-d');
        $endDate = $data['end_date'] ?? $absence->end_date->format('Y-m-d');

        if ($startDate < now()->format('Y-m-d')) {
            throw new \RuntimeException('Este estado ya fue procesado y no puede modificarse.');
        }

        $teacherExternalId = $data['teacher_external_id'] ?? $absence->teacher_external_id;

        // Buscar clases afectadas en el rango
        $affectedSchedules = $this->getAffectedSchedules($teacherExternalId, $startDate, $endDate);
        if ($affectedSchedules->isEmpty()) {
            throw new NoClassesInPeriodException;
        }

        $overlaps = $this->repository->findOverlappingAbsences($teacherExternalId, $startDate, $endDate, $id);

        if ($overlaps->isNotEmpty() && empty($data['is_confirmed'])) {
            throw new OverlapRequiredException(
                'El docente ya tiene una ausencia registrada en ese periodo.',
                $overlaps
            );
        }

        return DB::transaction(function () use ($absence, $data, $affectedSchedules) {
            $updatedAbsence = $this->repository->update($absence, $data);
            $updatedAbsence->classSchedules()->sync($affectedSchedules->pluck('class_schedule_id')->toArray());

            event(new TeacherAbsenceRegistered($updatedAbsence));

            return $updatedAbsence->fresh()->load('classSchedules.classroom', 'absenceType');
        });
    }

    /**
     * Elimina (soft delete) una ausencia limpiando la relación de la tabla pivote de forma manual.
     */
    public function delete(int $id): bool
    {
        $absence = $this->repository->findById($id);

        if (! $absence) {
            return false;
        }

        return DB::transaction(function () use ($absence) {
            $absence->classSchedules()->detach();

            return $this->repository->delete($absence);
        });
    }

    /**
     * Obtiene las clases programadas para un docente en un rango de fechas.
     *
     * @return \Illuminate\Support\Collection<int, ClassSchedule>
     */
    public function getAffectedSchedules(string $teacherExternalId, string $startDate, string $endDate): \Illuminate\Support\Collection
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Buscar los semestres que se traslapan con el rango de la ausencia
        $semesters = Semester::where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->get();

        $scheduleIds = [];

        foreach ($semesters as $semester) {
            $semStart = Carbon::parse($semester->start_date);
            $semEnd = Carbon::parse($semester->end_date);

            $interStart = $start->gt($semStart) ? $start->copy() : $semStart->copy();
            $interEnd = $end->lt($semEnd) ? $end->copy() : $semEnd->copy();

            $diffInDays = $interStart->diffInDays($interEnd);
            $weekdays = [];

            if ($diffInDays >= 7) {
                $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            } else {
                $curr = $interStart->copy();
                while ($curr <= $interEnd) {
                    $weekdays[] = strtolower($curr->englishDayOfWeek);
                    $curr->addDay();
                }
                $weekdays = array_unique($weekdays);
            }

            $schedules = ClassSchedule::where('semester_id', $semester->semester_id)
                ->where('teacher_external_id', $teacherExternalId)
                ->whereIn('weekday', $weekdays)
                ->where('status', true)
                ->pluck('class_schedule_id')
                ->toArray();

            $scheduleIds = array_merge($scheduleIds, $schedules);
        }

        if (empty($scheduleIds)) {
            return collect();
        }

        return ClassSchedule::whereIn('class_schedule_id', array_unique($scheduleIds))->get();
    }

    /**
     * Compila estadísticas de ausencias para un docente o todos.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getStats(array $filters = [], ?SamIdentity $user = null): array
    {
        $absences = $this->getAll($filters, $user);

        $totalAbsences = $absences->count();
        $totalDays = 0;

        foreach ($absences as $absence) {
            $start = Carbon::parse($absence->start_date);
            $end = Carbon::parse($absence->end_date);
            $totalDays += $start->diffInDays($end) + 1;
        }

        $byType = [];
        foreach ($absences->groupBy('absence_type_id') as $typeId => $group) {
            $byType[$typeId] = $group->count();
        }

        return [
            'totalAbsences' => $totalAbsences,
            'totalDaysAbsent' => $totalDays,
            'byType' => $byType,
        ];
    }
}
