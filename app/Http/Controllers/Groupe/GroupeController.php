<?php

namespace App\Http\Controllers\Groupe;

use App\Http\Controllers\Controller;
use App\Models\Groupe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class GroupeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('Administrateur')) {
            $groupes = Groupe::with(['responsable.roles', 'createur.roles', 'membres.roles'])->get();
        } elseif ($user->hasRole('Assistant')) {
            $groupes = Groupe::with(['responsable.roles', 'createur.roles', 'membres.roles'])
                ->where('responsable_id', $user->id)
                ->orWhere('created_by', $user->id)
                ->get();
        } else {
            $groupes = Groupe::with(['responsable.roles', 'createur.roles', 'membres.roles'])
                ->whereHas('membres', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->get();
        }

        return response()->json($groupes);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['Super_Admin', 'Administrateur', 'Assistant'])) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $request->validate([
            'nom' => 'required|string|max:255',
            'annee' => 'required|string|max:10',
            'description' => 'nullable|string',
            'responsable_id' => 'nullable|exists:users,id',
        ]);

        $groupe = Groupe::create([
            'nom' => $request->nom,
            'annee' => $request->annee,
            'description' => $request->description,
            'responsable_id' => $request->responsable_id,
            'created_by' => $user->id,
        ]);

        return response()->json($groupe, 201);
    }

    // Afficher un groupe
    public function show($id)
    {
        $groupe = Groupe::with(['responsable.roles', 'createur.roles', 'membres.roles'])->findOrFail($id);
        return response()->json($groupe);
    }

    // Mettre à jour un groupe
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['Super_Admin', 'Administrateur', 'Assistant'])) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $groupe = Groupe::findOrFail($id);

        $request->validate([
            'nom' => 'sometimes|string|max:255',
            'annee' => 'sometimes|string|max:10',
            'description' => 'nullable|string',
            'responsable_id' => 'nullable|exists:users,id',
        ]);

        $groupe->update($request->only(['nom', 'annee', 'description', 'responsable_id']));

        return response()->json($groupe);
    }

    // Supprimer un groupe
    public function destroy($id)
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['Super_Admin', 'Administrateur', 'Assistant'])) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $groupe = Groupe::findOrFail($id);
        $groupe->delete();

        return response()->json(['message' => 'Groupe supprimé']);
    }

    // Ajouter membre
    public function addMembre(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['Super_Admin', 'Administrateur', 'Assistant'])) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_in_groupe' => 'nullable|string'
        ]);

        $groupe = Groupe::findOrFail($id);
        $groupe->membres()->attach($request->user_id, ['role_in_groupe' => $request->role_in_groupe]);

        return response()->json(['message' => 'Membre ajouté']);
    }

    // Retirer membre
    public function removeMembre($id, $user_id)
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['Super_Admin', 'Administrateur', 'Assistant'])) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $groupe = Groupe::findOrFail($id);
        $groupe->membres()->detach($user_id);

        return response()->json(['message' => 'Membre retiré']);
    }
}
