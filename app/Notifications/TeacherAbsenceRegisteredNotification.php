<?php

/**
 * @descripcion  Notificación de base de datos para registrar ausencias de docentes.
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
 * @cambios      2026-05-25 - Creación inicial de la notificación.
 */

declare(strict_types=1);

namespace App\Notifications;

use App\Models\TeacherAbsence;
use Illuminate\Notifications\Notification;

class TeacherAbsenceRegisteredNotification extends Notification
{
    public function __construct(
        public readonly TeacherAbsence $absence
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'absence_id' => $this->absence->id,
            'teacher_external_id' => $this->absence->teacher_external_id,
            'absence_type_id' => $this->absence->absence_type_id,
            'start_date' => $this->absence->start_date->format('Y-m-d'),
            'end_date' => $this->absence->end_date->format('Y-m-d'),
            'observations' => $this->absence->observations,
            'message' => "El docente con ID {$this->absence->teacher_external_id} ha registrado una ausencia del {$this->absence->start_date->format('d/m/Y')} al {$this->absence->end_date->format('d/m/Y')}.",
        ];
    }
}
