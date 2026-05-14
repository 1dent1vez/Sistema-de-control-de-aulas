<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Qr\GamaQrCodeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;

class GenerateQrBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        private readonly array $classroomIds,
        private readonly string $format,
        private readonly string $batchId,
    ) {}

    public function handle(GamaQrCodeService $service): void
    {
        $service->downloadBatch($this->classroomIds, $this->format);
    }
}
