<?php

/**
 * @descripcion  Evento disparado al registrar o actualizar una ausencia docente.
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
 * @cambios      2026-05-25 - Creación inicial del evento.
 */

declare(strict_types=1);

namespace App\Events;

use App\Models\TeacherAbsence;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherAbsenceRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly TeacherAbsence $absence
    ) {}
}
