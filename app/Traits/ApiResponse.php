<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * @descripcion Trait para estandarizar las respuestas de la API
 *
 * @autor Agente
 *
 * @autorizador Agente
 *
 * @prueba N/A
 *
 * @mantenimiento N/A
 *
 * @version 1.0.0
 *
 * @creado 2026-05-13
 *
 * @modificado 2026-05-13
 *
 * @cambios Creación del trait ApiResponse
 */
trait ApiResponse
{
    /**
     * Respuesta exitosa estandarizada.
     */
    protected function success(mixed $data = null, string $message = 'Operación exitosa', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'statusCode' => $code,
            'message' => $message,
            'data' => $data,
            'errors' => [],
        ], $code);
    }

    /**
     * Respuesta de error estandarizada.
     */
    protected function error(string $message = 'Ocurrió un error', int $code = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'statusCode' => $code,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Respuesta de creación (201).
     */
    protected function created(mixed $data = null, string $message = 'Recurso creado exitosamente.'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Respuesta sin contenido (204).
     */
    protected function noContent(string $message = 'Sin contenido.'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'statusCode' => 204,
            'message' => $message,
            'data' => null,
            'errors' => [],
        ], 204);
    }
}
