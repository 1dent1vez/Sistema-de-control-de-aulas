<?php

/**
 * @descripcion  Enum que define los tipos de aula del sistema.
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

namespace App\Enums\Buildings;

enum ClassroomType: string
{
    case CLASSROOM = 'classroom';
    case COMPUTER_LAB = 'computer_lab';

    public function label(): string
    {
        return match ($this) {
            self::CLASSROOM => 'Salón',
            self::COMPUTER_LAB => 'Laboratorio de Cómputo',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
