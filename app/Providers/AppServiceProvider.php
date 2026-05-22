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

use App\Models\Building;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\Institution;
use App\Models\QrCode;
use App\Models\SamIdentity;
use App\Models\Semester;
use App\Models\TeacherAbsence;
use App\Policies\BuildingPolicy;
use App\Policies\ClassroomPolicy;
use App\Policies\ClassSchedulePolicy;
use App\Policies\InstitutionPolicy;
use App\Policies\QrCodePolicy;
use App\Policies\SamIdentityPolicy;
use App\Policies\SemesterPolicy;
use App\Policies\TeacherAbsencePolicy;
use App\Repositories\Auth\GamaSamIdentityRepository;
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
use App\Repositories\Contracts\QrCodeRepositoryInterface;
use App\Repositories\Contracts\SamIdentityRepositoryInterface;
use App\Repositories\Contracts\SemesterRepositoryInterface;
use App\Repositories\Contracts\TeacherAbsenceRepositoryInterface;
use App\Repositories\Qr\GamaQrCodeRepository;
use App\Repositories\Schedules\GamaClassScheduleRepository;
use App\Repositories\Schedules\GamaSemesterRepository;
use App\Repositories\TeacherStatus\GamaTeacherAbsenceRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->app->bind(TeacherAbsenceRepositoryInterface::class, GamaTeacherAbsenceRepository::class);
        $this->app->bind(QrCodeRepositoryInterface::class, GamaQrCodeRepository::class);
        $this->app->bind(SamIdentityRepositoryInterface::class, GamaSamIdentityRepository::class);
    }

    public function boot(): void
    {
        Gate::policy(Institution::class, InstitutionPolicy::class);
        Gate::policy(Building::class, BuildingPolicy::class);
        Gate::policy(Classroom::class, ClassroomPolicy::class);
        Gate::policy(Semester::class, SemesterPolicy::class);
        Gate::policy(ClassSchedule::class, ClassSchedulePolicy::class);
        Gate::policy(TeacherAbsence::class, TeacherAbsencePolicy::class);
        Gate::policy(QrCode::class, QrCodePolicy::class);
        Gate::policy(SamIdentity::class, SamIdentityPolicy::class);

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute((int) env('API_RATE_LIMIT', 60))
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute((int) env('AUTH_RATE_LIMIT', 10))
                ->by($request->ip());
        });
    }
}
