<?php

/**
 * @descripcion  Enum que define los roles locales del sistema (admin, teacher).
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
 * @creado       2026-05-17
 *
 * @modificado   2026-05-17
 *
 * @cambios      2026-05-17 - Creación inicial del enum
 */

declare(strict_types=1);

namespace App\Enums\Auth;

enum SamRole: string
{
    case ADMIN = 'admin';
    case TEACHER = 'teacher';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
