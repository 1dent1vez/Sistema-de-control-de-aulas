<?php

/**
 * @descripcion  Comando para purgar semestres caducados y sus horarios.
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
 * @cambios      2026-05-13 - Creación inicial del comando
 */

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Auth\SamRole;
use App\Models\SamIdentity;
use App\Notifications\PurgeFailedNotification;
use App\Repositories\Contracts\SemesterRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurgeExpiredSemestersCommand extends Command
{
    protected $signature = 'purge:expired-semesters';

    protected $description = 'Soft delete semestres caducados y sus horarios.';

    public function handle(SemesterRepositoryInterface $repository): int
    {
        $expired = $repository->getExpired();

        if ($expired->isEmpty()) {
            $this->info('No hay semestres caducados.');

            return Command::SUCCESS;
        }

        $successCount = 0;
        $admins = SamIdentity::where('role', SamRole::ADMIN)->get();

        foreach ($expired as $semester) {
            try {
                DB::transaction(function () use ($semester): void {
                    $semester->classSchedules()->delete();
                    $semester->delete();
                });

                $this->line("Semestre '{$semester->name}' (ID: {$semester->id}) purgado.");
                $successCount++;
            } catch (\Exception $e) {
                $errorMessage = "Error al purgar semestre caducado ID: {$semester->id}";
                Log::error($errorMessage.' - Detalle: '.$e->getMessage(), [
                    'semester_id' => $semester->id,
                    'exception' => $e,
                ]);
                $this->error($errorMessage);

                foreach ($admins as $admin) {
                    $admin->notify(new PurgeFailedNotification($semester, $e->getMessage()));
                }
            }
        }

        if ($successCount > 0) {
            $this->info("{$successCount} semestre(s) purgado(s).");
        }

        return Command::SUCCESS;
    }
}
