<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AllowanceController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    // User management (admin only)
    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });

    // Parent routes
    Route::middleware('parent')->group(function () {
        Route::get('/children', [UserController::class, 'children']);
        Route::post('/tasks/assign', [TaskController::class, 'assign']);
        Route::put('/tasks/{task}/verify', [TaskController::class, 'verify']);
        Route::post('/allowance/calculate', [AllowanceController::class, 'calculate']);
        Route::post('/allowance/payout', [AllowanceController::class, 'payout']);
    });

    // Child routes
    Route::middleware('child')->group(function () {
        Route::get('/tasks/assigned', [TaskController::class, 'assigned']);
        Route::put('/tasks/{task}/complete', [TaskController::class, 'complete']);
        Route::get('/allowance/history', [AllowanceController::class, 'history']);
    });

    // Common routes
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
});
