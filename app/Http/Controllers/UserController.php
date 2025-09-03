<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RequestUser;
use App\Models\Assistant;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $assistants = User::role('Assistant')->with('roles')->get();
        return response()->json($assistants, 200);
    }

    public function store(RequestUser $request)
    {
        try {
            $data = $request->validated();
            Log::info('Data reçue:', $data);

            $data['password'] = Hash::make('passer123');
            $user = User::create($data);
            $user->assignRole('Assistant');
            $user->hasRole('Assistant');

            Assistant::create([
                    'user_id' => $user->id
                ]);

            return response()->json([
                'message' => 'Utilisateur créé avec succès',
                'user'    => $user->load('roles'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $user = User::with('roles')->findOrFail($id);
        return response()->json($user);
    }
    public function update(RequestUser $request, $id)
    {
        try {
            $data = $request->validated();

            Log::info('Données validées pour update:', $data);

            $user = User::findOrFail($id);

            // Forcer le mot de passe
            $data['password'] = Hash::make('passer123');

            $user->update($data);

            return response()->json([
                'message' => 'Utilisateur mis à jour',
                'user'    => $user->load('roles'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Erreurs de validation
            Log::error('Erreur de validation lors de la mise à jour:', $e->errors());

            return response()->json([
                'message' => 'Erreur de validation',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Autres erreurs
            Log::error('Erreur lors de la mise à jour de l\'utilisateur: ' . $e->getMessage());

            return response()->json([
                'message' => 'Erreur serveur',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy ($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'Utilisateur supprimé avec succès']);
    }
}
