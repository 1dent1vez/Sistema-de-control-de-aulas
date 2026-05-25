<?php

/**
 * @descripcion  Listener para notificar a los administradores sobre ausencias docentes.
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
 * @creado       2026-05-25
 *
 * @modificado   2026-05-25
 *
 * @cambios      2026-05-25 - Creación inicial del listener.
 */

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\Auth\SamRole;
use App\Events\TeacherAbsenceRegistered;
use App\Models\SamIdentity;
use App\Notifications\TeacherAbsenceRegisteredNotification;
use Illuminate\Support\Facades\Notification;

class NotifyAdminOnAbsence
{
    public function handle(TeacherAbsenceRegistered $event): void
    {
        $absence = $event->absence;

        // Solo notificar si la ausencia fue registrada por un docente (no un admin)
        $teacher = SamIdentity::where('external_id', $absence->teacher_external_id)->first();
        if ($teacher && $teacher->role === SamRole::ADMIN) {
            return;
        }

        // Obtener todos los administradores
        $admins = SamIdentity::where('role', SamRole::ADMIN)->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new TeacherAbsenceRegisteredNotification($absence));
        }
    }
}
