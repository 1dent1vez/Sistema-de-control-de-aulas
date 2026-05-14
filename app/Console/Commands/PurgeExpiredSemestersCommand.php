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

use App\Repositories\Contracts\SemesterRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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

        DB::transaction(function () use ($expired): void {
            foreach ($expired as $semester) {
                $semester->classSchedules()->delete();
                $semester->delete();
                $this->line("Semestre '{$semester->name}' (ID: {$semester->id}) purgado.");
            }
        });

        $this->info("{$expired->count()} semestre(s) purgado(s).");

        return Command::SUCCESS;
    }
}
