<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse(mixed $data, string $message = 'Success.', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => $data,
            'errors' => [],
        ], $statusCode);
    }

    protected function createdResponse(mixed $data, string $message = 'Resource created successfully.'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    protected function noContentResponse(string $message = 'No content.'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'statusCode' => 204,
            'message' => $message,
            'data' => null,
            'errors' => [],
        ], 204);
    }

    protected function errorResponse(string $message = 'Error.', int $statusCode = 400, mixed $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $statusCode);
    }

    protected function notFoundResponse(string $message = 'Resource not found.'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    protected function validationErrorResponse(mixed $errors, string $message = 'Validation failed.'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    protected function unauthorizedResponse(string $message = 'Unauthenticated.'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    protected function forbiddenResponse(string $message = 'Forbidden.'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }
}
