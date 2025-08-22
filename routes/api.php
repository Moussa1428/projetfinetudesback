<?php

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

Route::get('SuperAdmin/users', [UserController::class, 'index']);
Route::post('SuperAdmin/users', [UserController::class, 'store']);
Route::get('SuperAdmin/users/{id}', [UserController::class, 'show']);
Route::put('SuperAdmin/users/{id}', [UserController::class, 'update']);
Route::delete('SuperAdmin/users/{id}', [UserController::class, 'destroy']);
