<?php

/**
 * @descripcion  Comando Artisan para reconciliar los horarios del docente de prueba "ghael.docente" en la base de datos.
 *
 * @autor        Antigravity <support@google.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Antigravity <support@google.com>
 *
 * @mantenimiento Antigravity <support@google.com>
 *
 * @version      1.0.0
 *
 * @creado       2026-05-29
 */

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ClassSchedule;
use App\Models\SamIdentity;
use App\Models\TeacherAbsence;
use App\Enums\Auth\SamRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixTeacherSchedules extends Command
{
    protected $signature = 'fix:teacher-schedules';

    protected $description = 'Reconcilia los horarios del docente 860 con el usuario de prueba ghael.docente.';

    public function handle(): int
    {
        $this->info('Iniciando reconciliación de horarios para ghael.docente...');

        // 1. Asegurar que existe la identidad ghael.docente
        $teacher = SamIdentity::where('external_id', 'ghael.docente')->first();
        if (!$teacher) {
            $this->line('Creando identidad local para ghael.docente...');
            $teacher = SamIdentity::create([
                'external_id' => 'ghael.docente',
                'email' => 'ghael.docente@toluca.tecnm.mx',
                'full_name' => 'Ghael Docente SAM',
                'role' => SamRole::TEACHER,
            ]);
            $this->info('Identidad ghael.docente creada exitosamente.');
        } else {
            $this->line('Identidad local ghael.docente ya existe.');
            if ($teacher->role !== SamRole::TEACHER) {
                $teacher->role = SamRole::TEACHER;
                $teacher->save();
                $this->info('Rol de ghael.docente actualizado a TEACHER.');
            }
        }

        // 2. Reconciliar schedules en BD de teacher_external_id = 860 -> ghael.docente
        $schedulesCount = ClassSchedule::where('teacher_external_id', '860')->count();
        if ($schedulesCount > 0) {
            $this->line("Se encontraron {$schedulesCount} horarios con teacher_external_id = '860'. Actualizando...");
            
            DB::transaction(function (): void {
                ClassSchedule::where('teacher_external_id', '860')
                    ->update(['teacher_external_id' => 'ghael.docente']);
                
                TeacherAbsence::where('teacher_external_id', '860')
                    ->update(['teacher_external_id' => 'ghael.docente']);
            });

            $this->info("{$schedulesCount} horarios actualizados exitosamente.");
        } else {
            $this->info('No se encontraron horarios para 860. Quizás ya fueron actualizados o no existen.');
        }

        // 3. Mostrar resumen actual de horarios para ghael.docente
        $currentCount = ClassSchedule::where('teacher_external_id', 'ghael.docente')->count();
        $this->info("Resumen final: ghael.docente tiene {$currentCount} horarios asignados.");

        return Command::SUCCESS;
    }
}
