<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClubController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\RegistrationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 1. Public API Endpoints
Route::post('/login', [AuthController::class, 'login']);

Route::get('/clubs', [ClubController::class, 'index']);
Route::get('/clubs/{id}', [ClubController::class, 'show']);

Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);

// 2. Protected Student/President API Endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Event Registrations
    Route::get('/registrations', [RegistrationController::class, 'index']);
    Route::post('/events/{id}/register', [RegistrationController::class, 'register']);

    // FCM Device Tokens
    Route::post('/fcm-token', [AuthController::class, 'updateFcmToken']);
});
