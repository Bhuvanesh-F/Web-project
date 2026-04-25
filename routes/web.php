<?php

use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Admin\AdminWebController;
use App\Http\Controllers\Doctor\DoctorWebController;
use App\Http\Controllers\Nurse\NurseWebController;
use App\Http\Controllers\Patient\PatientWebController;
use App\Http\Controllers\PetOwner\PetOwnerWebController;
use App\Http\Controllers\Receptionist\ReceptionistWebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| VitalCare Web Routes — web.php
|--------------------------------------------------------------------------
|
| Session-based routes for Blade/web views.
| API routes are in routes/api.php.
|
*/

// ============================================================
// PUBLIC ROUTES
// ============================================================
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicController::class, 'sendContact'])->name('contact.send');

// ============================================================
// GUEST AUTH ROUTES (login, register)
// ============================================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('auth.login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('login')
        ->middleware('throttle:5,1');

    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('auth.register');
    Route::post('/register', [WebAuthController::class, 'register'])->name('register')
        ->middleware('throttle:10,1');
});

// ============================================================
// LOGOUT (authenticated users only)
// ============================================================
Route::post('/logout', [WebAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ============================================================
// ADMIN DASHBOARD
// ============================================================
Route::prefix('admin')
    ->middleware(['auth', 'role:admin', 'secure.session'])
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminWebController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [AdminWebController::class, 'users'])->name('users');
        Route::get('/audit-logs', [AdminWebController::class, 'auditLogs'])->name('audit-logs');
    });

// ============================================================
// DOCTOR DASHBOARD
// ============================================================
Route::prefix('doctor')
    ->middleware(['auth', 'role:doctor', 'secure.session'])
    ->name('doctor.')
    ->group(function () {
        Route::get('/dashboard', [DoctorWebController::class, 'dashboard'])->name('dashboard');
        Route::get('/appointments', [DoctorWebController::class, 'appointments'])->name('appointments');
        Route::get('/patients', [DoctorWebController::class, 'patients'])->name('patients');
    });

// ============================================================
// NURSE DASHBOARD
// ============================================================
Route::prefix('nurse')
    ->middleware(['auth', 'role:nurse', 'secure.session'])
    ->name('nurse.')
    ->group(function () {
        Route::get('/dashboard', [NurseWebController::class, 'dashboard'])->name('dashboard');
        Route::get('/patients', [NurseWebController::class, 'patients'])->name('patients');
    });

// ============================================================
// PATIENT DASHBOARD
// ============================================================
Route::prefix('patient')
    ->middleware(['auth', 'role:patient', 'secure.session'])
    ->name('patient.')
    ->group(function () {
        Route::get('/dashboard', [PatientWebController::class, 'dashboard'])->name('dashboard');
        Route::get('/appointments', [PatientWebController::class, 'appointments'])->name('appointments');
        Route::get('/records', [PatientWebController::class, 'records'])->name('records');
    });

// ============================================================
// PET OWNER DASHBOARD
// ============================================================
Route::prefix('pet-owner')
    ->middleware(['auth', 'role:pet_owner', 'secure.session'])
    ->name('pet-owner.')
    ->group(function () {
        Route::get('/dashboard', [PetOwnerWebController::class, 'dashboard'])->name('dashboard');
        Route::get('/pets', [PetOwnerWebController::class, 'pets'])->name('pets');
    });

// ============================================================
// RECEPTIONIST DASHBOARD
// ============================================================
Route::prefix('receptionist')
    ->middleware(['auth', 'role:receptionist', 'secure.session'])
    ->name('receptionist.')
    ->group(function () {
        Route::get('/dashboard', [ReceptionistWebController::class, 'dashboard'])->name('dashboard');
        Route::get('/appointments', [ReceptionistWebController::class, 'appointments'])->name('appointments');
    });
