<?php

declare(strict_types=1);

namespace App\Http\Resources\Qr;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QrCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'classroomId' => $this->classroom_id,
            'token' => $this->token,
            'payload' => $this->payload,
            'fileUrl' => $this->file_path ? url("api/v1/qr-codes/{$this->id}/file") : null,
            'isActive' => $this->is_active,
            'generatedAt' => $this->generated_at?->toISOString(),
            'invalidatedAt' => $this->invalidated_at?->toISOString(),
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
}
