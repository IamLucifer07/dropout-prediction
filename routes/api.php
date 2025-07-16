<?php

// routes/api.php
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExternalDataController;
use App\Http\Controllers\Api\CollegeAdminController;
// use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::apiResource('students', StudentController::class);
    Route::post('/students/{student}/predict', [StudentController::class, 'makePrediction']);

    Route::get('/external-data', [ExternalDataController::class, 'index']);
    Route::post('/external-data/sync', [ExternalDataController::class, 'syncData']);
});

// College Admin routes
Route::apiResource('college-admins', CollegeAdminController::class);

// Student routes
Route::apiResource('students', StudentController::class);

// active college admins
Route::get('/college-admins/active', [CollegeAdminController::class, 'getActiveAdmins']);
