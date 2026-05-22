<?php

/**
 * @descripcion  Rutas web del Sistema de Control de Aulas
 *
 * @autor        Equipo GAMA
 *
 * @version      1.1.0
 *
 * @modificado   2026-05-19
 *
 * @cambios      2026-05-19 - Rutas protegidas agrupadas bajo middleware sam.auth
 */

declare(strict_types=1);

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
        return view('dashboard');
    })->name('dashboard');

    Route::get('/docente/estatus', function () {
        return view('docente.estatus');
    })->name('docente.estatus');

    // Rutas exclusivas para administradores
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard/admin', function () {
            return view('dashboard.admin');
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

        Route::get('/usuarios', function () {
            return view('usuarios.index');
        })->name('usuarios');

        Route::get('/codigosqr', function () {
            return view('qr.index');
        })->name('codigosqr');

        Route::get('/configuracion', function () {
            return view('configuracion.index');
        })->name('configuracion');
    });
});
