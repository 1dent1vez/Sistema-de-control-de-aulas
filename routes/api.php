<?php

/**
 * @descripcion  Rutas de la API versión 1.
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
 * @creado       2026-05-13
 *
 * @modificado   2026-05-26
 *
 * @cambios      2026-05-13 - Creación inicial de las rutas API
 *               2026-05-26 - Registro de la ruta de confirmación de importación masiva.
 */

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\SamIdentityController;
use App\Http\Controllers\Api\V1\Buildings\GamaBuildingController;
use App\Http\Controllers\Api\V1\Buildings\GamaClassroomController;
use App\Http\Controllers\Api\V1\Catalogs\GamaAbsenceTypeController;
use App\Http\Controllers\Api\V1\Catalogs\GamaInstitutionController;
use App\Http\Controllers\Api\V1\Qr\GamaQrCodeController;
use App\Http\Controllers\Api\V1\Schedules\GamaClassScheduleController;
use App\Http\Controllers\Api\V1\Schedules\GamaScheduleImportController;
use App\Http\Controllers\Api\V1\Schedules\GamaSemesterController;
use App\Http\Controllers\Api\V1\TeacherStatus\GamaCheckOverlapController;
use App\Http\Controllers\Api\V1\TeacherStatus\GamaTeacherAbsenceController;
use App\Http\Controllers\Api\V1\TeacherStatus\GamaTeacherAbsenceStatsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {

    // Catálogos - Institutions (reads público, writes admin)
    Route::prefix('institutions')->name('institutions.')->group(function () {
        Route::get('/', [GamaInstitutionController::class, 'index'])->name('index');
        Route::post('/', [GamaInstitutionController::class, 'store'])->name('store')->middleware('auth:sanctum');
        Route::get('/{institutionId}', [GamaInstitutionController::class, 'show'])->name('show');
        Route::put('/{institutionId}', [GamaInstitutionController::class, 'update'])->name('update')->middleware('auth:sanctum');
        Route::delete('/{institutionId}', [GamaInstitutionController::class, 'destroy'])->name('destroy')->middleware('auth:sanctum');
    });

    // Catálogos - Absence Types (público)
    Route::prefix('absence-types')->name('absence-types.')->group(function () {
        Route::get('/', [GamaAbsenceTypeController::class, 'index'])->name('index');
        Route::get('/{absenceTypeId}', [GamaAbsenceTypeController::class, 'show'])->name('show');
    });

    // Buildings (reads público, writes admin)
    Route::prefix('buildings')->name('buildings.')->group(function () {
        Route::get('/', [GamaBuildingController::class, 'index'])->name('index');
        Route::post('/', [GamaBuildingController::class, 'store'])->name('store')->middleware('auth:sanctum');
        Route::get('/{buildingId}', [GamaBuildingController::class, 'show'])->name('show');
        Route::put('/{buildingId}', [GamaBuildingController::class, 'update'])->name('update')->middleware('auth:sanctum');
        Route::delete('/{buildingId}', [GamaBuildingController::class, 'destroy'])->name('destroy')->middleware('auth:sanctum');
        Route::get('/{buildingId}/levels', [GamaBuildingController::class, 'levels'])->name('levels');
        Route::get('/{buildingId}/classrooms', [GamaClassroomController::class, 'byBuilding'])->name('classrooms');
    });

    // Classrooms (reads público, writes admin)
    Route::prefix('classrooms')->name('classrooms.')->group(function () {
        Route::get('/', [GamaClassroomController::class, 'index'])->name('index');
        Route::post('/', [GamaClassroomController::class, 'store'])->name('store')->middleware('auth:sanctum');
        Route::get('/{classroomId}', [GamaClassroomController::class, 'show'])->name('show');
        Route::put('/{classroomId}', [GamaClassroomController::class, 'update'])->name('update')->middleware('auth:sanctum');
        Route::delete('/{classroomId}', [GamaClassroomController::class, 'destroy'])->name('destroy')->middleware('auth:sanctum');
    });

    // Semesters (reads público, writes admin)
    Route::prefix('semesters')->name('semesters.')->group(function () {
        Route::get('/', [GamaSemesterController::class, 'index'])->name('index');
        Route::get('/current', [GamaSemesterController::class, 'current'])->name('current');
        Route::post('/', [GamaSemesterController::class, 'store'])->name('store')->middleware('auth:sanctum');
        Route::get('/{semesterId}', [GamaSemesterController::class, 'show'])->name('show');
        Route::put('/{semesterId}', [GamaSemesterController::class, 'update'])->name('update')->middleware('auth:sanctum');
        Route::delete('/{semesterId}', [GamaSemesterController::class, 'destroy'])->name('destroy')->middleware('auth:sanctum');
    });

    // Class Schedules (reads público, writes admin)
    Route::prefix('class-schedules')->name('class-schedules.')->group(function () {
        Route::get('/', [GamaClassScheduleController::class, 'index'])->name('index');
        Route::post('/', [GamaClassScheduleController::class, 'store'])->name('store')->middleware(['auth:sanctum', 'role:admin']);
        Route::post('/import', [GamaScheduleImportController::class, '__invoke'])->name('import')->middleware(['auth:sanctum', 'role:admin']);
        Route::post('/import/confirm', [GamaScheduleImportController::class, 'confirm'])->name('import.confirm')->middleware(['auth:sanctum', 'role:admin']);
        Route::get('/import/{batchId}/report', [GamaScheduleImportController::class, 'report'])->name('report')->middleware(['auth:sanctum', 'role:admin']);
        Route::get('/{scheduleId}', [GamaClassScheduleController::class, 'show'])->name('show');
        Route::put('/{scheduleId}', [GamaClassScheduleController::class, 'update'])->name('update')->middleware(['auth:sanctum', 'role:admin']);
        Route::delete('/{scheduleId}', [GamaClassScheduleController::class, 'destroy'])->name('destroy')->middleware(['auth:sanctum', 'role:admin']);
    });

    // QR Codes
    Route::prefix('classrooms/{classroomId}/qr')->name('classrooms.qr.')->group(function () {
        Route::post('/', [GamaQrCodeController::class, 'generate'])->name('generate')->middleware('auth:sanctum');
        Route::get('/', [GamaQrCodeController::class, 'show'])->name('show');
    });

    Route::prefix('qr-codes')->name('qr-codes.')->group(function () {
        Route::post('/download', [GamaQrCodeController::class, 'download'])->name('download')->middleware('auth:sanctum');
        Route::get('/download/{batchId}/status', [GamaQrCodeController::class, 'downloadStatus'])->name('download.status')->middleware('auth:sanctum');
        Route::get('/download/file/{batchId}', [GamaQrCodeController::class, 'downloadFile'])->name('download.file');
        Route::get('/{id}/file', [GamaQrCodeController::class, 'file'])->name('file');
    });

    // Auth — público (rate limit: auth)
    Route::match(['get', 'post'], 'auth/captcha', [AuthController::class, 'captcha'])->name('auth.captcha')->middleware('throttle:auth');
    Route::post('auth/validate-captcha', [AuthController::class, 'validateCaptcha'])->name('auth.validate-captcha')->middleware('throttle:auth');
    Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login')->middleware('throttle:auth');

    // Auth + SamIdentities — protegido
    Route::middleware('auth:sanctum')->group(function () {
        // Teacher Absences (Privado)
        Route::prefix('teacher-absences')->name('teacher-absences.')->group(function () {
            Route::get('/', [GamaTeacherAbsenceController::class, 'index'])->name('index');
            Route::post('/', [GamaTeacherAbsenceController::class, 'store'])->name('store');
            Route::get('/stats', GamaTeacherAbsenceStatsController::class)->name('stats');
            Route::get('/check-overlap', [GamaCheckOverlapController::class, '__invoke'])->name('check-overlap');
            Route::get('/{absenceId}', [GamaTeacherAbsenceController::class, 'show'])->name('show');
            Route::put('/{absenceId}', [GamaTeacherAbsenceController::class, 'update'])->name('update');
            Route::delete('/{absenceId}', [GamaTeacherAbsenceController::class, 'destroy'])->name('destroy');
        });

        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');

        Route::get('sam-identities', [SamIdentityController::class, 'index'])->name('sam-identities.index');
        Route::get('sam-identities/teachers', [SamIdentityController::class, 'searchLocalTeachers'])->name('sam-identities.teachers');
        Route::get('sam-identities/search', [SamIdentityController::class, 'search'])->name('sam-identities.search');
        Route::get('sam/empleados', [SamIdentityController::class, 'searchSamEmployees'])->middleware('role:admin')->name('sam.empleados.search');
        Route::post('sam-identities/set-password', [SamIdentityController::class, 'setPassword'])->name('sam-identities.set-password');
        Route::post('sam-identities/{externalId}/assign-role', [SamIdentityController::class, 'assignRole'])->name('sam-identities.assign-role');
        Route::delete('sam-identities/{externalId}', [SamIdentityController::class, 'destroy'])->name('sam-identities.destroy');
    });

});
