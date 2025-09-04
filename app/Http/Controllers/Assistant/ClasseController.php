<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Classe\ClasseRequest;
use App\Models\Assistant;
use App\Models\Classe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClasseController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('Assistant')) {
            // L'assistant voit uniquement les classes qu’il a créées
            $classes = Classe::with('etudiants.user')
                ->where('created_by', $user->id)
                ->get();
        } elseif ($user->hasRole('Administrateur')) {
            // L’admin voit les classes créées par ses assistants
            $assistantIds = $user->assistants()->pluck('user_id');
            $classes = Classe::with('etudiants.user')
                ->whereIn('created_by', $assistantIds)
                ->get();
        } elseif ($user->hasRole('Etudiant')) {
            // L’étudiant voit uniquement sa classe
            $etudiant = $user->etudiant;
            if (!$etudiant) {
                return response()->json(['message' => 'Aucune classe associée'], 404);
            }

            $classes = Classe::with('etudiants.user')
                ->where('id', $etudiant->classe_id)
                ->get();
        } else {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        return response()->json($classes);
    }


    public function store(ClasseRequest $request)
    {
        $data = $request->validated();

        // Générer un code unique pour la classe
        $data['code'] = $this->generateUniqueCode($data['filiere'], $data['niveau'], $data['anneeacademique']);

        // Vérifier unicité du code (optionnel, pour être sûr)
        $suffix = 1;
        $original_code = $data['code'];
        while (Classe::where('code', $data['code'])->exists()) {
            $data['code'] = $original_code . '-' . $suffix;
            $suffix++;
        }

        $classe = Classe::create($data);

        return response()->json($classe, 201);
    }

    public function storeclasse(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('Assistant')) {
            return response()->json(['message' => 'Seul un assistant peut créer une classe'], 403);
        }

        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'niveau' => 'required|string|max:255',
            'filiere' => 'required|string|max:255',
            'anneeacademique' => 'required|string|max:255',
        ]);

        // Récupérer l’assistant lié au user connecté
        $assistant = Assistant::where('user_id', $user->id)->first();
        if (!$assistant) {
            return response()->json(['message' => 'Cet assistant n’est lié à aucun administrateur'], 400);
        }

        // Récupérer le user_id de l’administrateur
        $admin = $assistant->admin;
        if (!$admin || !$admin->user_id) {
            return response()->json(['message' => 'Aucun administrateur associé à cet assistant'], 400);
        }

        // Générer un code unique pour la classe
        $data['code'] = $this->generateUniqueCode($data['filiere'], $data['niveau'], $data['anneeacademique']);

        // Ajouter les champs supplémentaires
        $data['created_by'] = $user->id;           // id de l'assistant
        $data['responsable_id'] = $admin->user_id; // id du user administrateur

        $classe = Classe::create($data);

        return response()->json([
            'message' => 'Classe créée avec succès',
            'classe' => $classe
        ], 201);
    }



    private function generateUniqueCode($filiere, $niveau, $anneeacademique)
    {
        $filiereCode = $this->getFiliereCode($filiere);
        $baseCode = "{$filiereCode}-{$niveau}-{$anneeacademique}"; // Exemple : GL-L1-2025

        $code = $baseCode;
        $counter = 1;

        // Vérifier si le code existe déjà
        while (Classe::where('code', $code)->exists()) {
            $code = "{$baseCode}-{$counter}";
            $counter++;
        }

        return $code;
    }

    private function getFiliereCode($filiere)
    {
        $filiereCodes = [
            'Genie Logiciel' => 'GL',
            'Reseaux et Systeme' => 'RS',
            'Intelligence Artificielle' => 'IA',
            'Ingénierie de Données' => 'ID',
            'Informatique Appliquée a la Gestion des Entreprises' => 'IAGE',
        ];

        return $filiereCodes[$filiere] ?? strtoupper(substr(str_replace(' ', '', $filiere), 0, 3));
    }


    public function update(Request $request, $id)
    {
        $classe = Classe::findOrFail($id);

        $data = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'niveau' => 'sometimes|string|max:255',
            'filiere' => 'sometimes|string|max:255',
            'anneeacademique' => 'sometimes|string|max:255',
            'status' => 'sometimes|boolean'
        ]);

        $classe->update($data);

        return response()->json($classe, 200);
    }

    public function destroy($id)
    {
        $classe = Classe::find($id);

        if (!$classe) {
            return response()->json(['message' => 'Classe non trouvée'], 404);
        }

        $classe->delete();
        return response()->json(['message' => 'Classe supprimée avec succès'], 200);
    }

    public function show($id)
    {
        $classe = Classe::with('etudiants.user')->find($id);

        if (!$classe) {
            return response()->json(['message' => 'Classe non trouvée'], 404);
        }

        return response()->json($classe, 200);
    }

    /**
     * Activer / Désactiver une classe
     */
    public function toggleStatus($id)
    {
        $user = Auth::user();
        $classe = Classe::find($id);

        if (!$classe) {
            return response()->json(['message' => 'Classe non trouvée'], 404);
        }

        // Vérifier les droits :
        // - Assistant peut changer ses classes
        // - Admin peut changer les classes de ses assistants
        if ($user->hasRole('Assistant') && $classe->created_by !== $user->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        } elseif ($user->hasRole('Administrateur')) {
            $assistantIds = $user->assistants()->pluck('user_id')->toArray();
            if (!in_array($classe->created_by, $assistantIds)) {
                return response()->json(['message' => 'Non autorisé'], 403);
            }
        } elseif (!$user->hasRole('Assistant') && !$user->hasRole('Administrateur')) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        // Basculer le status
        $classe->status = !$classe->status;
        $classe->save();

        return response()->json([
            'message' => 'Status mis à jour avec succès',
            'classe' => $classe
        ], 200);
    }
}
