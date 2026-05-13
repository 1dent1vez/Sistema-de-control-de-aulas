<?php

use Illuminate\Support\Facades\Route;

// Principal Routes
Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/edificios', function () {
    return view('edificios.index');
})->name('edificios');

Route::get('/aulas', function () {
    return view('aulas.index');
})->name('aulas');

Route::get('/aulas/horario-publico', function () {
    return view('aulas.horario-publico');
})->name('aulas.horario_publico');

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

Route::get('/docente/estatus', function () {
    return view('docente.estatus');
})->name('docente.estatus');

Route::get('/dashboard/admin', function () {
    return view('dashboard.admin');
})->name('dashboard.admin');

// Authentication Routes...
Route::get('/login', function () {
    return view('login');
})->name('login');

// Terms and Conditions
Route::get('/terms', function () {
    return view('legal.terms');
})->name('terms');
