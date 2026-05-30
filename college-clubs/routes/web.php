<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ClubManagerController;
use App\Http\Controllers\President\DashboardController;
use App\Http\Controllers\President\EventController;
use App\Http\Controllers\President\ReportController;

// 1. Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// 2. Authenticated Logout Route
Route::match(['get', 'post'], '/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// 3. Admin Routes
Route::prefix('admin')
    ->middleware(['auth', 'role:admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // Password Management Routes
        Route::get('/password', [LoginController::class, 'showPasswordForm'])->name('password.form');
        Route::post('/password', [LoginController::class, 'changePassword'])->name('password.update');
        
        // Club Management Routes
        Route::get('/clubs', [ClubManagerController::class, 'index'])->name('clubs.index');
        Route::post('/clubs', [ClubManagerController::class, 'store'])->name('clubs.store');
        Route::delete('/clubs/{club}', [ClubManagerController::class, 'destroy'])->name('clubs.destroy');
    });

// 4. President Routes (fully scoped to their own club via scope.club)
Route::prefix('president')
    ->middleware(['auth', 'role:president', 'scope.club'])
    ->name('president.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Password Management Routes
        Route::get('/password', [LoginController::class, 'showPasswordForm'])->name('password.form');
        Route::post('/password', [LoginController::class, 'changePassword'])->name('password.update');
        
        // Event CRUD Routes
        Route::get('/events', [EventController::class, 'index'])->name('events.index');
        Route::post('/events/upload-pdf', [EventController::class, 'uploadPdf'])->name('events.upload-pdf');
        Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
        Route::post('/events', [EventController::class, 'store'])->name('events.store');
        Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
        Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
        Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

        // Reports Routing
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });
