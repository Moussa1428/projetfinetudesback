<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    // Route::get('SuperAdmin/users', [UserController::class, 'index']);
    // Route::post('SuperAdmin/users', [UserController::class, 'store']);
    // Route::get('SuperAdmin/users/{id}', [UserController::class, 'show']);
    // Route::put('SuperAdmin/users/{id}', [UserController::class, 'update']);
    // Route::delete('SuperAdmin/users/{id}', [UserController::class, 'destroy']);

});

// gestion des assistants
Route::get('SuperAdmin/users', [UserController::class, 'index']);
Route::post('SuperAdmin/users', [UserController::class, 'store']);
Route::get('SuperAdmin/users/{id}', [UserController::class, 'show']);
Route::put('SuperAdmin/users/{id}', [UserController::class, 'update']);
Route::delete('SuperAdmin/users/{id}', [UserController::class, 'destroy']);

//gestion des administrateurs
Route::get('/administrateurs', [AdminController::class, 'index']);
Route::post('/administrateurs', [AdminController::class, 'store']);
Route::get('/administrateurs/{id}', [AdminController::class, 'show']);
Route::put('/administrateurs/{id}', [AdminController::class, 'update']);
Route::delete('/administrateurs/{id}', [AdminController::class, 'destroy']);
