<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Assistant\ClasseController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Etudiant\EtudiantController;
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

//gestion des classes
Route::get('classes', [ClasseController::class, 'index']);        // Liste toutes les classes
Route::post('classes', [ClasseController::class, 'store']);       // Crée une nouvelle classe
Route::get('classes/{classe}', [ClasseController::class, 'show']); // Affiche une classe
Route::put('classes/{classe}', [ClasseController::class, 'update']); // Met à jour une classe
Route::delete('classes/{classe}', [ClasseController::class, 'destroy']);

//gestion des etudiants
// Liste tous les étudiants
Route::get('etudiants', [EtudiantController::class, 'index']);// Crée un ou plusieurs étudiants
Route::post('etudiants', [EtudiantController::class, 'store']);// Affiche un étudiant
Route::get('etudiants/{etudiant}', [EtudiantController::class, 'show']);// Met à jour un étudiant
Route::put('etudiants/{etudiant}', [EtudiantController::class, 'update']);// Supprime un étudiant
Route::delete('etudiants/{etudiant}', [EtudiantController::class, 'destroy']);
