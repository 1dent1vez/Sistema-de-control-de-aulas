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
 * @version      1.0.0
 *
 * @creado       2026-05-13
 *
 * @modificado   2026-05-13
 *
 * @cambios      2026-05-13 - Creación inicial de las rutas API
 */

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Buildings\GamaBuildingController;
use App\Http\Controllers\Api\V1\Buildings\GamaClassroomController;
use App\Http\Controllers\Api\V1\Catalogs\GamaAbsenceTypeController;
use App\Http\Controllers\Api\V1\Catalogs\GamaInstitutionController;
use App\Http\Controllers\Api\V1\Schedules\GamaClassScheduleController;
use App\Http\Controllers\Api\V1\Schedules\GamaSemesterController;
use App\Http\Controllers\Api\V1\TeacherStatus\GamaTeacherAbsenceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {

    // Catálogos - Institutions
    Route::prefix('institutions')->name('institutions.')->group(function () {
        Route::get('/', [GamaInstitutionController::class, 'index'])->name('index');
        Route::post('/', [GamaInstitutionController::class, 'store'])->name('store');
        Route::get('/{institutionId}', [GamaInstitutionController::class, 'show'])->name('show');
        Route::put('/{institutionId}', [GamaInstitutionController::class, 'update'])->name('update');
        Route::delete('/{institutionId}', [GamaInstitutionController::class, 'destroy'])->name('destroy');
    });

    // Catálogos - Absence Types
    Route::prefix('absence-types')->name('absence-types.')->group(function () {
        Route::get('/', [GamaAbsenceTypeController::class, 'index'])->name('index');
        Route::get('/{absenceTypeId}', [GamaAbsenceTypeController::class, 'show'])->name('show');
    });

    // Buildings
    Route::prefix('buildings')->name('buildings.')->group(function () {
        Route::get('/', [GamaBuildingController::class, 'index'])->name('index');
        Route::post('/', [GamaBuildingController::class, 'store'])->name('store');
        Route::get('/{buildingId}', [GamaBuildingController::class, 'show'])->name('show');
        Route::put('/{buildingId}', [GamaBuildingController::class, 'update'])->name('update');
        Route::delete('/{buildingId}', [GamaBuildingController::class, 'destroy'])->name('destroy');
        Route::get('/{buildingId}/levels', [GamaBuildingController::class, 'levels'])->name('levels');
        Route::get('/{buildingId}/classrooms', [GamaClassroomController::class, 'byBuilding'])->name('classrooms');
    });

    // Classrooms
    Route::prefix('classrooms')->name('classrooms.')->group(function () {
        Route::get('/', [GamaClassroomController::class, 'index'])->name('index');
        Route::post('/', [GamaClassroomController::class, 'store'])->name('store');
        Route::get('/{classroomId}', [GamaClassroomController::class, 'show'])->name('show');
        Route::put('/{classroomId}', [GamaClassroomController::class, 'update'])->name('update');
        Route::delete('/{classroomId}', [GamaClassroomController::class, 'destroy'])->name('destroy');
    });

    // Semesters
    Route::prefix('semesters')->name('semesters.')->group(function () {
        Route::get('/', [GamaSemesterController::class, 'index'])->name('index');
        Route::get('/current', [GamaSemesterController::class, 'current'])->name('current');
        Route::post('/', [GamaSemesterController::class, 'store'])->name('store');
        Route::get('/{semesterId}', [GamaSemesterController::class, 'show'])->name('show');
        Route::put('/{semesterId}', [GamaSemesterController::class, 'update'])->name('update');
        Route::delete('/{semesterId}', [GamaSemesterController::class, 'destroy'])->name('destroy');
    });

    // Class Schedules
    Route::prefix('class-schedules')->name('class-schedules.')->group(function () {
        Route::get('/', [GamaClassScheduleController::class, 'index'])->name('index');
        Route::post('/', [GamaClassScheduleController::class, 'store'])->name('store');
        Route::post('/import', [GamaClassScheduleController::class, 'import'])->name('import');
        Route::get('/{scheduleId}', [GamaClassScheduleController::class, 'show'])->name('show');
        Route::put('/{scheduleId}', [GamaClassScheduleController::class, 'update'])->name('update');
        Route::delete('/{scheduleId}', [GamaClassScheduleController::class, 'destroy'])->name('destroy');
        Route::get('/import/{batchId}/report', [GamaClassScheduleController::class, 'report'])->name('report');
    });

    // Teacher Absences
    Route::prefix('teacher-absences')->name('teacher-absences.')->group(function () {
        Route::get('/', [GamaTeacherAbsenceController::class, 'index'])->name('index');
        Route::post('/', [GamaTeacherAbsenceController::class, 'store'])->name('store');
        Route::get('/check-overlap', [GamaTeacherAbsenceController::class, 'checkOverlap'])->name('check-overlap');
        Route::get('/{absenceId}', [GamaTeacherAbsenceController::class, 'show'])->name('show');
        Route::put('/{absenceId}', [GamaTeacherAbsenceController::class, 'update'])->name('update');
        Route::delete('/{absenceId}', [GamaTeacherAbsenceController::class, 'destroy'])->name('destroy');
    });

});
