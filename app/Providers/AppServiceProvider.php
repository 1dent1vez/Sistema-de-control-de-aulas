<?php

/**
 * @descripcion  Service provider principal donde se registran bindings de repositorios.
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
 * @cambios      2026-05-13 - Registro de bindings de repositorios
 */

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Buildings\GamaBuildingRepository;
use App\Repositories\Buildings\GamaClassroomRepository;
use App\Repositories\Buildings\GamaLevelRepository;
use App\Repositories\Catalogs\GamaAbsenceTypeRepository;
use App\Repositories\Catalogs\GamaInstitutionRepository;
use App\Repositories\Contracts\AbsenceTypeRepositoryInterface;
use App\Repositories\Contracts\BuildingRepositoryInterface;
use App\Repositories\Contracts\ClassroomRepositoryInterface;
use App\Repositories\Contracts\ClassScheduleRepositoryInterface;
use App\Repositories\Contracts\InstitutionRepositoryInterface;
use App\Repositories\Contracts\LevelRepositoryInterface;
use App\Repositories\Contracts\SemesterRepositoryInterface;
use App\Repositories\Schedules\GamaClassScheduleRepository;
use App\Repositories\Schedules\GamaSemesterRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InstitutionRepositoryInterface::class, GamaInstitutionRepository::class);
        $this->app->bind(AbsenceTypeRepositoryInterface::class, GamaAbsenceTypeRepository::class);
        $this->app->bind(BuildingRepositoryInterface::class, GamaBuildingRepository::class);
        $this->app->bind(LevelRepositoryInterface::class, GamaLevelRepository::class);
        $this->app->bind(ClassroomRepositoryInterface::class, GamaClassroomRepository::class);
        $this->app->bind(SemesterRepositoryInterface::class, GamaSemesterRepository::class);
        $this->app->bind(ClassScheduleRepositoryInterface::class, GamaClassScheduleRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
