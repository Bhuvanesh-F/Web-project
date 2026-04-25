<?php

use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\MedicalRecordController;
use App\Http\Controllers\Api\NurseController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\PetController;
use App\Http\Controllers\Api\PetOwnerController;
use App\Http\Controllers\Api\ReceptionistController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| VitalCare API Routes — api.php
|--------------------------------------------------------------------------
|
| All routes here are prefixed with /api (set in RouteServiceProvider).
|
| Auth routes (login, register) are public — no sanctum guard needed.
| All other routes are protected by sanctum + role middleware.
|
| Rate limiting: throttle:5,1 on auth endpoints (5 req/min) to prevent
| brute-force attacks. General API: throttle:60,1.
|
*/

// ============================================================
// PUBLIC AUTH ROUTES — No authentication required
// ============================================================
Route::prefix('auth')->group(function () {

    // POST /api/auth/login
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('api.auth.login');

    // POST /api/auth/register
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:10,1')
        ->name('api.auth.register');
});

// ============================================================
// PROTECTED AUTH ROUTES — Sanctum token required
// ============================================================
Route::prefix('auth')->middleware('auth:sanctum')->group(function () {

    // POST /api/auth/logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('api.auth.logout');

    // GET /api/auth/me
    Route::get('/me', [AuthController::class, 'me'])
        ->name('api.auth.me');
});

// Health check - PUBLIC, no auth needed
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => 'VitalCare',
        'timestamp' => now(),
    ]);
});

// ============================================================
// GENERAL PROTECTED ROUTES — Sanctum token required
// ============================================================
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    // ── ADMIN ────────────────────────────────────────────────
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminApiController::class, 'dashboard'])->name('api.admin.dashboard');
        Route::get('/users', [AdminApiController::class, 'users'])->name('api.admin.users');
        Route::post('/users', [AdminApiController::class, 'createUser'])->name('api.admin.users.create');
        Route::put('/users/{id}', [AdminApiController::class, 'updateUser'])->name('api.admin.users.update');
        Route::delete('/users/{id}', [AdminApiController::class, 'deleteUser'])->name('api.admin.users.delete');
        Route::get('/statistics', [AdminApiController::class, 'statistics'])->name('api.admin.statistics');
        Route::get('/audit-logs', [AdminApiController::class, 'auditLogs'])->name('api.admin.audit-logs');  
    });

    // ── DOCTORS ──────────────────────────────────────────────
    Route::prefix('doctors')->middleware('role:admin,doctor')->group(function () {
        Route::get('/', [DoctorController::class, 'index'])->name('api.doctors.index');
        Route::get('/{id}', [DoctorController::class, 'show'])->name('api.doctors.show');
        Route::post('/', [DoctorController::class, 'store'])->middleware('role:admin')->name('api.doctors.store');
        Route::put('/{id}', [DoctorController::class, 'update'])->name('api.doctors.update');
        Route::delete('/{id}', [DoctorController::class, 'destroy'])->middleware('role:admin')->name('api.doctors.destroy');
    });

    // ── PATIENTS ─────────────────────────────────────────────
    Route::prefix('patients')->middleware('role:admin,doctor,nurse,receptionist')->group(function () {
        Route::get('/', [PatientController::class, 'index'])->name('api.patients.index');
        Route::get('/{id}', [PatientController::class, 'show'])->name('api.patients.show');
        Route::post('/', [PatientController::class, 'store'])->name('api.patients.store');
        Route::put('/{id}', [PatientController::class, 'update'])->name('api.patients.update');
        Route::delete('/{id}', [PatientController::class, 'destroy'])->middleware('role:admin')->name('api.patients.destroy');
    });

    // ── APPOINTMENTS ─────────────────────────────────────────
    Route::prefix('appointments')->middleware('role:admin,doctor,nurse,receptionist,patient')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->name('api.appointments.index');
        Route::get('/{id}', [AppointmentController::class, 'show'])->name('api.appointments.show');
        Route::post('/', [AppointmentController::class, 'store'])->name('api.appointments.store');
        Route::put('/{id}', [AppointmentController::class, 'update'])->name('api.appointments.update');
        Route::delete('/{id}', [AppointmentController::class, 'destroy'])->name('api.appointments.destroy');
    });

    // ── MEDICAL RECORDS ──────────────────────────────────────
    Route::prefix('records')->middleware('role:admin,doctor,nurse,patient')->group(function () {
        Route::get('/', [MedicalRecordController::class, 'index'])->name('api.records.index');
        Route::get('/{id}', [MedicalRecordController::class, 'show'])->name('api.records.show');
        Route::post('/', [MedicalRecordController::class, 'store'])->middleware('role:admin,doctor')->name('api.records.store');
        Route::put('/{id}', [MedicalRecordController::class, 'update'])->middleware('role:admin,doctor')->name('api.records.update');
        Route::delete('/{id}', [MedicalRecordController::class, 'destroy'])->middleware('role:admin')->name('api.records.destroy');
    });

    // ── NURSES ───────────────────────────────────────────────
    Route::prefix('nurses')->middleware('role:admin,nurse')->group(function () {
        Route::get('/', [NurseController::class, 'index'])->name('api.nurses.index');
        Route::get('/{id}', [NurseController::class, 'show'])->name('api.nurses.show');
        Route::post('/', [NurseController::class, 'store'])->middleware('role:admin')->name('api.nurses.store');
        Route::put('/{id}', [NurseController::class, 'update'])->name('api.nurses.update');
        Route::delete('/{id}', [NurseController::class, 'destroy'])->middleware('role:admin')->name('api.nurses.destroy');
    });

    // ── RECEPTIONISTS ────────────────────────────────────────
    Route::prefix('receptionists')->middleware('role:admin,receptionist')->group(function () {
        Route::get('/', [ReceptionistController::class, 'index'])->name('api.receptionists.index');
        Route::get('/{id}', [ReceptionistController::class, 'show'])->name('api.receptionists.show');
        Route::post('/', [ReceptionistController::class, 'store'])->middleware('role:admin')->name('api.receptionists.store');
        Route::put('/{id}', [ReceptionistController::class, 'update'])->name('api.receptionists.update');
        Route::delete('/{id}', [ReceptionistController::class, 'destroy'])->middleware('role:admin')->name('api.receptionists.destroy');
    });

    // ── PET OWNERS ───────────────────────────────────────────
    Route::prefix('pet-owners')->middleware('role:admin,pet_owner')->group(function () {
        Route::get('/', [PetOwnerController::class, 'index'])->name('api.pet-owners.index');
        Route::get('/{id}', [PetOwnerController::class, 'show'])->name('api.pet-owners.show');
        Route::post('/', [PetOwnerController::class, 'store'])->name('api.pet-owners.store');
        Route::put('/{id}', [PetOwnerController::class, 'update'])->name('api.pet-owners.update');
        Route::delete('/{id}', [PetOwnerController::class, 'destroy'])->middleware('role:admin')->name('api.pet-owners.destroy');
    });

    // ── PETS ─────────────────────────────────────────────────
    Route::prefix('pets')->middleware('role:admin,vet,pet_owner')->group(function () {
        Route::get('/', [PetController::class, 'index'])->name('api.pets.index');
        Route::get('/{id}', [PetController::class, 'show'])->name('api.pets.show');
        Route::post('/', [PetController::class, 'store'])->name('api.pets.store');
        Route::put('/{id}', [PetController::class, 'update'])->name('api.pets.update');
        Route::delete('/{id}', [PetController::class, 'destroy'])->name('api.pets.destroy');
    });

    // ── REVIEWS ──────────────────────────────────────────────
    Route::prefix('reviews')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('api.reviews.index');
        Route::get('/{id}', [ReviewController::class, 'show'])->name('api.reviews.show');
        Route::post('/', [ReviewController::class, 'store'])->middleware('role:patient,pet_owner')->name('api.reviews.store');
        Route::delete('/{id}', [ReviewController::class, 'destroy'])->middleware('role:admin')->name('api.reviews.destroy');
    });

    // ── CONTACT ──────────────────────────────────────────────
    Route::post('/contact', [ContactController::class, 'send'])->name('api.contact.send');


});
