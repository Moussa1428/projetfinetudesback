<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Assistant\ClasseController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Enseignant\EnseignantController;
use App\Http\Controllers\Etudiant\EtudiantController;
use App\Http\Controllers\Groupe\GroupeController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\NotificationAPI\NotificationController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('classes', [ClasseController::class, 'index']);
    Route::post('classes', [ClasseController::class, 'storeclasse']);
    Route::get('classes/{classe}', [ClasseController::class, 'show']);
    Route::put('classes/{classe}', [ClasseController::class, 'update']);
    Route::delete('classes/{classe}', [ClasseController::class, 'destroy']);
    Route::post('etudiants/import', [ImportController::class, 'importEtudiant']);
    Route::post('classes/import-etudiants', [ImportController::class, 'importEtudiantCreationClasse']);
    Route::put('classes/{classe}/toggle-status', [ClasseController::class, 'toggleStatus']);



    //2 gestion des assistants par  admin qui fait gestion des assistants cbgererfini
    Route::get('assistants/users', [UserController::class, 'index']);
    Route::post('assistants/users', [UserController::class, 'store']);
    Route::get('assistants/users/{id}', [UserController::class, 'show']);
    Route::put('assistants/users/{id}', [UserController::class, 'update']);
    Route::delete('assistants/users/{id}', [UserController::class, 'destroy']);
    Route::put('assistants/users/{id}/toggle', [UserController::class, 'toggleAssistant']);
    Route::get('/users/responsables', [UserController::class, 'responsables']);



    //1 gestion des administrateurs super admin qui fait gestion des administrateurs cbgererfini
    Route::get('/administrateurs', [AdminController::class, 'index']);
    Route::post('/administrateurs', [AdminController::class, 'store']);
    Route::get('/administrateurs/{id}', [AdminController::class, 'show']);
    Route::put('/administrateurs/{id}', [AdminController::class, 'update']);
    Route::delete('/administrateurs/{id}', [AdminController::class, 'destroy']);
    Route::put('administrateurs/{id}/toggle-active', [AdminController::class, 'toggleActive']);



    //gestion des etudiants
    Route::get('etudiants', [EtudiantController::class, 'index']);
    Route::post('etudiants', [EtudiantController::class, 'store']);
    Route::get('etudiants/{etudiant}', [EtudiantController::class, 'show']);
    Route::put('etudiants/{etudiant}', [EtudiantController::class, 'update']);
    Route::put('etudiants/{etudiant}/toggle-active', [EtudiantController::class, 'toggleActive']);



    //3 gestion des groupes par assistant et admin qui fait gestion des groupes cbgererfini pour admin reste à faire pour assistant
    Route::get('/groupes', [GroupeController::class, 'index']);
    Route::post('/groupes', [GroupeController::class, 'store']);
    Route::get('/groupes/{id}', [GroupeController::class, 'show']);
    Route::put('/groupes/{id}', [GroupeController::class, 'update']);
    Route::delete('/groupes/{id}', [GroupeController::class, 'destroy']);
    Route::patch('groupes/{id}/toggle-active', [GroupeController::class, 'toggleActive']);
    Route::post('/groupes/{id}/membres', [GroupeController::class, 'addMembre']);
    Route::delete('/groupes/{id}/membres/{user_id}', [GroupeController::class, 'removeMembre']);


    //4 gestion des enseignants par admin et assistant
    Route::get('enseignants', [EnseignantController::class, 'index']);
    Route::post('enseignants', [EnseignantController::class, 'store']);
    Route::get('enseignants/{id}', [EnseignantController::class, 'show']);
    Route::put('enseignants/{id}', [EnseignantController::class, 'update']);
    Route::delete('enseignants/{id}', [EnseignantController::class, 'destroy']);
    Route::put('enseignants/{id}/toggle-active', [EnseignantController::class, 'toggleActive']);


    Route::post('/notifications/send', [NotificationController::class, 'send']);
    Route::get('/notifications', [NotificationController::class, 'getNotifications']);

});

// // gestion des assistants
// Route::get('SuperAdmin/users', [UserController::class, 'index']);
// Route::post('SuperAdmin/users', [UserController::class, 'store']);
// Route::get('SuperAdmin/users/{id}', [UserController::class, 'show']);
// Route::put('SuperAdmin/users/{id}', [UserController::class, 'update']);
// Route::delete('SuperAdmin/users/{id}', [UserController::class, 'destroy']);

// //gestion des administrateurs
// Route::get('/administrateurs', [AdminController::class, 'index']);
// Route::post('/administrateurs', [AdminController::class, 'store']);
// Route::get('/administrateurs/{id}', [AdminController::class, 'show']);
// Route::put('/administrateurs/{id}', [AdminController::class, 'update']);
// Route::delete('/administrateurs/{id}', [AdminController::class, 'destroy']);

// //gestion des classes
// Route::get('classes', [ClasseController::class, 'index']);        // Liste toutes les classes
// Route::post('classes', [ClasseController::class, 'store']);       // Crée une nouvelle classe
// Route::get('classes/{classe}', [ClasseController::class, 'show']); // Affiche une classe
// Route::put('classes/{classe}', [ClasseController::class, 'update']); // Met à jour une classe
// Route::delete('classes/{classe}', [ClasseController::class, 'destroy']);

// //gestion des etudiants
// // Liste tous les étudiants
// Route::get('etudiants', [EtudiantController::class, 'index']); // Crée un ou plusieurs étudiants
// Route::post('etudiants', [EtudiantController::class, 'store']); // Affiche un étudiant
// Route::get('etudiants/{etudiant}', [EtudiantController::class, 'show']); // Met à jour un étudiant
// Route::put('etudiants/{etudiant}', [EtudiantController::class, 'update']); // Supprime un étudiant
// Route::delete('etudiants/{etudiant}', [EtudiantController::class, 'destroy']);
