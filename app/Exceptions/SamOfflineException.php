<?php

/**
 * @descripcion  Excepción lanzada cuando no es posible establecer conexión con el servicio SAM externo.
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
 * @creado       2026-05-24
 *
 * @modificado   2026-05-24
 *
 * @cambios      2026-05-24 - Creación de la excepción de conexión SAM externa
 */

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class SamOfflineException extends RuntimeException
{
    public function __construct(string $message = 'Servicio de directorio no disponible.')
    {
        parent::__construct($message);
    }
}
