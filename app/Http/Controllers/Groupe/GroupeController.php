<?php

namespace App\Http\Controllers\Groupe;

use App\Http\Controllers\Controller;
use App\Models\Groupe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class GroupeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('Super_Admin')) {
            // Super admin voit tout
            $groupes = Groupe::with(['responsable.roles', 'createur.roles', 'membres.roles'])->get();
        } elseif ($user->hasRole('Administrateur')) {
            // Admin → ses groupes + ceux créés par ses assistants
            $groupes = Groupe::with(['responsable.roles', 'createur.roles', 'membres.roles'])
            ->where('created_by', $user->id)
            ->orWhere('responsable_id', $user->id)
            ->get();
        } elseif ($user->hasRole('Assistant')) {
            // Assistant → uniquement ses groupes
            $groupes = Groupe::with(['responsable.roles', 'createur.roles', 'membres.roles'])
                ->where('created_by', $user->id)
                ->orWhere('responsable_id', $user->id)
                ->get();
        } else {
            // Étudiant → uniquement ses groupes
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

        if ($user->hasRole('Assistant') && empty($request->responsable_id)) {
            $assistant = $user->assistantforgroupe; // récupère la ligne de la table assistants
            if ($assistant && $assistant->admin) {
                // récupère l'id du user administrateur réel
                $responsableId = $assistant->admin->user_id;
            } else {
                return response()->json(['message' => 'Aucun administrateur associé à cet assistant'], 400);
            }
        } else {
            $responsableId = $request->responsable_id;
        }

        $groupe = Groupe::create([
            'nom' => $request->nom,
            'annee' => $request->annee,
            'description' => $request->description,
            'responsable_id' => $responsableId,
            'created_by' => $user->id,
        ]);



        return response()->json($groupe, 201);
    }

    public function show($id)
    {
        $groupe = Groupe::with(['responsable.roles', 'createur.roles', 'membres.roles'])->findOrFail($id);
        return response()->json($groupe);
    }

    // Mettre à jour un groupe
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $groupe = Groupe::findOrFail($id);

        // Vérification : seul le créateur peut modifier
        if ($user->id !== $groupe->created_by && !$user->hasRole('Super_Admin')) {
            return response()->json(['message' => 'Vous n’êtes pas autorisé à modifier ce groupe.'], 403);
        }

        $request->validate([
            'nom' => 'sometimes|string|max:255',
            'annee' => 'sometimes|string|max:10',
            'description' => 'nullable|string',
            'responsable_id' => 'nullable|exists:users,id',
            'is_active' => 'nullable|boolean',
        ]);

        $groupe->update($request->only(['nom', 'annee', 'description', 'responsable_id', 'is_active']));

        return response()->json([
            'message' => 'Groupe modifié avec succès',
            'groupe' => $groupe
        ]);
    }


    public function destroy($id)
    {
        $user = Auth::user();
        $groupe = Groupe::findOrFail($id);

        // Seul le créateur ou Super_Admin peut supprimer
        if ($user->id !== $groupe->created_by && !$user->hasRole('Super_Admin')) {
            return response()->json(['message' => 'Vous n’êtes pas autorisé à supprimer ce groupe.'], 403);
        }

        $groupe->delete();
        return response()->json(['message' => 'Groupe supprimé']);
    }

    // Ajouter membre
    public function addMembre(Request $request, $id)
    {
        $user = Auth::user();
        $groupe = Groupe::findOrFail($id);

        // Seul le créateur ou le responsable peut ajouter un membre
        if ($user->id !== $groupe->created_by && $user->id !== $groupe->responsable_id) {
            return response()->json(['message' => 'Non autorisé à ajouter des membres'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $membre = User::findOrFail($request->user_id);

        // Vérifie si le membre est déjà dans le groupe
        if ($groupe->membres()->where('user_id', $membre->id)->exists()) {
            return response()->json(['message' => 'Cet utilisateur est déjà membre du groupe.'], 400);
        }

        // Récupère le rôle principal de l'utilisateur
        $role_in_groupe = $membre->roles->pluck('name')->first();

        // Ajoute le membre dans le groupe avec son rôle
        $groupe->membres()->attach($membre->id, [
            'role_in_groupe' => $role_in_groupe
        ]);

        return response()->json([
            'message' => 'Membre ajouté avec succès',
            'role_in_groupe' => $role_in_groupe
        ]);
    }

    // Retirer membre
    public function removeMembre($id, $user_id)
    {
        $user = Auth::user();
        $groupe = Groupe::findOrFail($id);

        // Seul le créateur, le responsable ou le Super_Admin peut retirer des membres
        if ($user->id !== $groupe->created_by && $user->id !== $groupe->responsable_id && !$user->hasRole('Super_Admin')) {
            return response()->json(['message' => 'Non autorisé à retirer des membres'], 403);
        }

        $groupe->membres()->detach($user_id);

        return response()->json(['message' => 'Membre retiré']);
    }

    // Activer / désactiver un groupe
    public function toggleActive($id)
    {
        $user = Auth::user();
        $groupe = Groupe::findOrFail($id);

        // Seul le créateur, le responsable ou le Super_Admin peut activer/désactiver
        if ($user->id !== $groupe->created_by && $user->id !== $groupe->responsable_id && !$user->hasRole('Super_Admin')) {
            return response()->json(['message' => 'Non autorisé à changer le statut'], 403);
        }

        $groupe->is_active = !$groupe->is_active;
        $groupe->save();

        return response()->json([
            'message' => 'Statut du groupe mis à jour',
            'is_active' => $groupe->is_active
        ]);
    }
}
