<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestUser;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $admins = User::role('Administrateur')->with('roles')->get();
        return response()->json($admins, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RequestUser $request)
    {
        try {
            $data = $request->validated();
            Log::info('Données reçues pour création Administrateur:', $data);

            $data['password'] = Hash::make('passer123'); // mot de passe par défaut
            $user = User::create($data);

            // Assigner le rôle Administrateur
            $user->assignRole('Administrateur');

            Admin::create([
                    'user_id' => $user->id
                ]);

            return response()->json([
                'message' => 'Administrateur créé avec succès',
                'user'    => $user->load('roles'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erreur création Administrateur: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with('roles')->findOrFail($id);
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RequestUser $request, $id)
    {
        try {
            $data = $request->validated();
            Log::info('Données validées pour update Administrateur:', $data);

            $user = User::findOrFail($id);

            // Forcer le mot de passe par défaut
            $data['password'] = Hash::make('passer123');

            $user->update($data);

            // Maintenir le rôle Administrateur
            $user->syncRoles('Administrateur');

            return response()->json([
                'message' => 'Administrateur mis à jour',
                'user'    => $user->load('roles'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erreur validation update Administrateur:', $e->errors());
            return response()->json([
                'message' => 'Erreur de validation',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur serveur update Administrateur: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur serveur',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Administrateur supprimé avec succès']);
    }
}
