<?php

/**
 * @descripcion  Excepción lanzada cuando un docente intenta registrar una ausencia
 *              en un período donde no tiene clases asignadas.
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
 * @cambios      2026-05-25 - Creación inicial de la excepción.
 */

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class NoClassesInPeriodException extends RuntimeException
{
    public function __construct(string $message = 'No tienes clases asignadas en el período seleccionado.')
    {
        parent::__construct($message);
    }
}
