<?php

/**
 * @descripcion  Notificación de base de datos para fallos en la purga de semestres caducados.
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
 * @creado       2026-05-25
 *
 * @modificado   2026-05-25
 *
 * @cambios      2026-05-25 - Creación inicial de la notificación.
 */

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Semester;
use Illuminate\Notifications\Notification;

class PurgeFailedNotification extends Notification
{
    public function __construct(
        public readonly Semester $semester,
        public readonly string $error
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'semester_id' => $this->semester->id,
            'semester_name' => $this->semester->name,
            'error' => $this->error,
            'message' => "Error al purgar semestre caducado ID: {$this->semester->id} / Nombre: {$this->semester->name}. Detalle: {$this->error}",
        ];
    }
}
