<?php

/**
 * @descripcion  Enum que define los códigos fijos de tipos de ausencia del sistema.
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
 * @cambios      2026-05-13 - Creación inicial del enum
 */

declare(strict_types=1);

namespace App\Enums\Catalogs;

enum AbsenceTypeCode: string
{
    case COMISION = 'comision';
    case JUNTA = 'junta';
    case INCAPACIDAD = 'incapacidad';
    case PERMISO_ECONOMICO = 'permiso_economico';
    case OTRO = 'otro';
}
