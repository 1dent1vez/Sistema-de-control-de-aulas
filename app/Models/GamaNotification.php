<?php

/**
 * @descripcion  Modelo Eloquent para la entidad GamaNotification (gama_notifications).
 *              Extiende el modelo de notificaciones base de Laravel para soportar el prefijo de tabla gama_.
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
 * @cambios      2026-05-25 - Creación inicial del modelo custom de notificación.
 */

declare(strict_types=1);

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification as BaseNotification;

class GamaNotification extends BaseNotification
{
    protected $table = 'notifications';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';
}
