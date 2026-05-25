<?php

/**
 * @descripcion  Rutas web del Sistema de Control de Aulas
 *
 * @autor        Equipo GAMA
 *
 * @version      1.1.1
 *
 * @modificado   2026-05-25
 *
 * @cambios      2026-05-19 - Rutas protegidas agrupadas bajo middleware sam.auth
 *               2026-05-25 - Importación de SamRole y uso de match en la ruta /dashboard para control estricto de roles.
 *               2026-05-25 - Adición de ruta web /admin/teacher-absences para la gestión administrativa de ausencias.
 */

declare(strict_types=1);

use App\Enums\Auth\SamRole;
use Illuminate\Support\Facades\Route;

// Rutas públicas (sin autenticación)
Route::get('/', function () {
    return view('login');
})->name('login');

Route::get('/aulas/horario-publico', function () {
    return view('aulas.horario-publico');
})->name('aulas.horario_publico');

Route::get('/terms', function () {
    return view('legal.terms');
})->name('terms');

Route::get('/aviso-de-privacidad', function () {
    return view('legal.privacy');
})->name('privacy');

// Rutas protegidas (requieren token Sanctum vía cabecera o cookie sam_token)
Route::middleware('sam.auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if (! $user || ! $user->role) {
            return redirect()->route('espera.rol');
        }

        return match ($user->role) {
            SamRole::ADMIN => view('dashboard.admin'),
            SamRole::TEACHER => view('docente.dashboard'),
        };
    })->name('dashboard');

    Route::get('/admin/dashboard', function () {
        return view('dashboard.admin');
    })->middleware('role:admin')->name('admin.dashboard');

    Route::get('/docente/dashboard', function () {
        return view('docente.dashboard');
    })->middleware('role:teacher')->name('docente.dashboard');

    Route::get('/espera-rol', function () {
        if (auth()->user()?->role !== null) {
            return redirect()->route('dashboard');
        }

        return view('espera-rol');
    })->name('espera.rol');

    Route::get('/docente/estatus', function () {
        return view('docente.estatus');
    })->name('docente.estatus');

    // Rutas exclusivas para administradores
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard/admin', function () {
            return redirect()->route('admin.dashboard');
        })->name('dashboard.admin');

        Route::get('/edificios', function () {
            return view('edificios.index');
        })->name('edificios');

        Route::get('/aulas', function () {
            return view('aulas.index');
        })->name('aulas');

        Route::get('/horarios/manual', function () {
            return view('horarios.manual');
        })->name('horarios.manual');

        Route::get('/horarios/importar', function () {
            return view('horarios.importar');
        })->name('horarios.importar');

        Route::get('/horarios/semestres', function () {
            return view('horarios.semestres.index');
        })->name('horarios.semestres.index');

        Route::get('/usuarios', function () {
            return view('usuarios.index');
        })->name('usuarios');

        Route::get('/codigosqr', function () {
            return view('qr.index');
        })->name('codigosqr');

        Route::get('/configuracion', function () {
            return view('configuracion.index');
        })->name('configuracion');

        Route::get('/admin/teacher-absences', function () {
            return view('admin.teacher-absences.index');
        })->name('admin.teacher-absences.index');
    });
});
