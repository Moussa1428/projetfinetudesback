<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Etudiant\EtudiantRequest;
use App\Http\Requests\RequestUser;
use App\Models\Classe;
use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EtudiantController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('Assistant')) {
            // Récupérer les classes créées par l'assistant
            $classes = Classe::where('created_by', $user->id)->pluck('id');

            $etudiants = Etudiant::with('user', 'classe')
                ->whereIn('classe_id', $classes)
                ->get();
        } elseif ($user->hasRole('Etudiant')) {
            $etudiant = $user->etudiant;
            if (!$etudiant) {
                return response()->json(['message' => 'Aucun étudiant associé'], 404);
            }

            $etudiants = Etudiant::with('user', 'classe')
                ->where('id', $etudiant->id)
                ->get();
        } else {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        return response()->json($etudiants);
    }

    // Affiche un étudiant
    public function show($userId)
    {
        $etudiant = Etudiant::with(['user.roles', 'classe'])
            ->where('user_id', $userId)
            ->first();

        if (!$etudiant) {
            return response()->json(['message' => 'Étudiant non trouvé'], 404);
        }

        return response()->json($etudiant, 200);
    }
    // Crée un étudiant
    public function store(RequestUser $requestUser, EtudiantRequest $requestEtudiant)
    {
        try {
            $dataUser = $requestUser->validated();
            $dataUser['password'] = Hash::make('passer123');

            // Vérification email & phone uniques
            if (User::where('email', $dataUser['email'])->exists()) {
                return response()->json(['message' => 'Cet email est déjà utilisé.'], 422);
            }
            if (User::where('phone', $dataUser['phone'])->exists()) {
                return response()->json(['message' => 'Ce numéro de téléphone est déjà utilisé.'], 422);
            }

            // Création User
            $user = User::create($dataUser);
            $user->assignRole('Etudiant');

            // Récupération classe
            $classe = Classe::findOrFail($requestEtudiant->classe_id);

            // Génération du matricule
            $totalEtudiants = Etudiant::count() + 1; // numéro global
            $numeroClasse = Etudiant::where('classe_id', $classe->id)->count() + 1; // numéro dans la classe
            $matricule = $totalEtudiants . '-' . $classe->anneeacademique . '-' . $numeroClasse . '/ISI';

            // Création étudiant
            $dataEtudiant = $requestEtudiant->validated();
            $dataEtudiant['matricule'] = $matricule;
            $etudiant = $user->etudiant()->create($dataEtudiant);

            return response()->json([
                'message' => 'Étudiant créé avec succès',
                'etudiant' => $etudiant->load(['user.roles', 'classe'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    // Met à jour un étudiant
    public function update(RequestUser $requestUser, $userId)
    {
        try {
            // Trouver l'étudiant via son user_id
            $etudiant = Etudiant::with('user')->where('user_id', $userId)->firstOrFail();
            $user = $etudiant->user;

            $dataUser = array_filter($requestUser->validated(), fn($v) => $v !== null);

            // Validation unique email et phone
            if (isset($dataUser['email']) && User::where('email', $dataUser['email'])->where('id', '!=', $user->id)->exists()) {
                return response()->json(['message' => 'Cet email est déjà utilisé.'], 422);
            }
            if (isset($dataUser['phone']) && User::where('phone', $dataUser['phone'])->where('id', '!=', $user->id)->exists()) {
                return response()->json(['message' => 'Ce numéro de téléphone est déjà utilisé.'], 422);
            }

            // ⚡ On garde toujours passer123 comme mot de passe par défaut
            if (!empty($dataUser['password'])) {
                $dataUser['password'] = Hash::make($dataUser['password']);
            } else {
                unset($dataUser['password']);
            }

            // Mise à jour uniquement des infos user
            if (!empty($dataUser)) {
                $user->update($dataUser);
            }

            return response()->json([
                'message' => 'Informations de l’étudiant mises à jour avec succès',
                'etudiant' => $etudiant->load(['user.roles', 'classe'])
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Activer/Désactiver un étudiant
    public function toggleActive($userId)
    {
        try {
            $etudiant = Etudiant::with('user')->where('user_id', $userId)->firstOrFail();
            $user = $etudiant->user;

            $user->is_active = !$user->is_active;
            $user->save();

            Log::info('Statut étudiant changé', ['user_id' => $userId, 'is_active' => $user->is_active]);

            return response()->json([
                'message' => $user->is_active ? 'Étudiant activé' : 'Étudiant désactivé',
                'etudiant' => $etudiant->load(['user.roles', 'classe'])
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
