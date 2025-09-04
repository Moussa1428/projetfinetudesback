<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Etudiant\EtudiantRequest;
use App\Http\Requests\RequestUser;
use App\Models\Classe;
use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Http\Request;
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
    public function show($etudiant)
    {
        $etudiant = Etudiant::with(['user.roles', 'classe'])->findOrFail($etudiant);
        return response()->json($etudiant, 200);
    }

    // Crée un étudiant
    public function store(RequestUser $requestUser, EtudiantRequest $requestEtudiant)
    {
        try {
            $dataUser = $requestUser->validated();
            $dataUser['password'] = Hash::make('passer123');

            // Vérification unique email et phone
            if (User::where('email', $dataUser['email'])->exists()) {
                return response()->json(['message' => 'Cet email est déjà utilisé.'], 422);
            }
            if (User::where('phone', $dataUser['phone'])->exists()) {
                return response()->json(['message' => 'Ce numéro de téléphone est déjà utilisé.'], 422);
            }

            $user = User::create($dataUser);
            $user->assignRole('Etudiant');

            $classe = Classe::findOrFail($requestEtudiant->classe_id);
            $totalEtudiants = Etudiant::count() + 1;
            $numeroClasse = Etudiant::where('classe_id', $classe->id)->count() + 1;
            $matricule = $totalEtudiants . '-' . $classe->anneeacademique . '-' . $numeroClasse . '/ISI';

            $dataEtudiant = $requestEtudiant->validated();
            $dataEtudiant['matricule'] = $matricule;
            $etudiant = $user->etudiant()->create($dataEtudiant);

            Log::info('Étudiant créé', ['user_id' => $user->id, 'etudiant_id' => $etudiant->id, 'matricule' => $matricule]);

            return response()->json([
                'message' => 'Étudiant créé avec succès',
                'etudiant' => $etudiant->load(['user.roles', 'classe'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Met à jour un étudiant
    public function update(RequestUser $requestUser, EtudiantRequest $requestEtudiant, $etudiant)
    {
        try {
            $etudiant = Etudiant::with('user')->findOrFail($etudiant);
            $userId = $etudiant->user->id;

            $dataUser = array_filter($requestUser->validated(), fn($v) => $v !== null);

            // Validation unique
            if (isset($dataUser['email']) && User::where('email', $dataUser['email'])->where('id', '!=', $userId)->exists()) {
                return response()->json(['message' => 'Cet email est déjà utilisé.'], 422);
            }
            if (isset($dataUser['phone']) && User::where('phone', $dataUser['phone'])->where('id', '!=', $userId)->exists()) {
                return response()->json(['message' => 'Ce numéro de téléphone est déjà utilisé.'], 422);
            }

            if (!empty($dataUser['password'])) {
                $dataUser['password'] = Hash::make($dataUser['password']);
            } else {
                unset($dataUser['password']);
            }

            if (!empty($dataUser)) {
                $etudiant->user->update($dataUser);
            }

            $dataEtudiant = array_filter($requestEtudiant->validated(), fn($v) => $v !== null);

            if (isset($dataEtudiant['classe_id']) && $etudiant->classe_id != $dataEtudiant['classe_id']) {
                $nouvelleClasse = Classe::findOrFail($dataEtudiant['classe_id']);
                $totalEtudiants = Etudiant::count();
                $numeroClasse = Etudiant::where('classe_id', $nouvelleClasse->id)->count() + 1;
                $dataEtudiant['matricule'] = $totalEtudiants . '-' . $nouvelleClasse->anneeacademique . '-' . $numeroClasse . '/ISI';
            }

            if (!empty($dataEtudiant)) {
                $etudiant->update($dataEtudiant);
            }

            Log::info('Étudiant mis à jour', ['etudiant_id' => $etudiant->id, 'user_id' => $userId]);

            return response()->json([
                'message' => 'Étudiant mis à jour avec succès',
                'etudiant' => $etudiant->load(['user.roles', 'classe'])
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour Étudiant', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Supprime un étudiant
    public function destroy($etudiant)
    {
        try {
            $etudiant = Etudiant::with('user')->findOrFail($etudiant);
            $etudiant->user->delete(); // Supprime aussi l’utilisateur
            $etudiant->delete();

            Log::info('Étudiant supprimé', ['etudiant_id' => $etudiant->id]);

            return response()->json(['message' => 'Étudiant supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
