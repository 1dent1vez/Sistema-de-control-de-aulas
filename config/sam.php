<?php

/**
 * @descripcion  Configuración del servicio de autenticación SAM.
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
 * @creado       2026-05-19
 *
 * @modificado   2026-05-19
 *
 * @cambios      2026-05-19 - Creación del archivo de configuración SAM
 */

declare(strict_types=1);

return [

    'mock_enabled' => (bool) env('SAM_MOCK_ENABLED', false),

    'verify_ssl' => (bool) env('SAM_VERIFY_SSL', false),

];
