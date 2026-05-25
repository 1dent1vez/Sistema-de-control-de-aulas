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
 * @version      1.1.0
 *
 * @creado       2026-05-19
 *
 * @modificado   2026-05-24
 *
 * @cambios      2026-05-19 - Creación del archivo de configuración SAM
 *               2026-05-24 - Adición de force_ip_resolve para forzar IPv4 en Guzzle
 */

declare(strict_types=1);

return [

    'url' => rtrim((string) env('SAM_URL', 'http://192.168.1.74:8090/SAM'), '/').'/',

    'timeout' => (float) env('SAM_TIMEOUT', 5.0),

    'connect_timeout' => (float) env('SAM_CONNECT_TIMEOUT', 3.0),

    'force_ip_resolve' => 'v4', // FORZAR IPv4 para evitar timeout en Windows

    'mock_enabled' => (bool) env('SAM_MOCK_ENABLED', false),

    'verify_ssl' => (bool) env('SAM_VERIFY_SSL', false),

];
