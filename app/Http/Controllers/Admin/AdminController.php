<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestUser;
use App\Mail\AdminCreated;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    /**
     * Vérifie que l'utilisateur connecté est Super_Admin
     */
    private function checkSuperAdmin()
    {
        $user = auth('sanctum')->user(); // Utilisateur connecté
        if (!$user || !auth('sanctum')->user()->hasRole('Super_Admin')) {
            abort(403, 'Accès refusé. Rôle Super_Admin requis.');
        }
    }

    /**
     * Liste tous les administrateurs
     */
    public function index()
    {
        $this->checkSuperAdmin();

        $admins = User::role('Administrateur')->with('roles')->get();
        return response()->json($admins, 200);
    }

    /**
     * Crée un nouvel administrateur
     */
    public function store(RequestUser $request)
    {
        $this->checkSuperAdmin();

        try {
            $data = $request->validated();
            Log::info('Données reçues pour création Administrateur:', $data);


            // Générer un mot de passe aléatoire de 8 caractères
            $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);

            $data['password'] = Hash::make($password); // mot de passe par défaut
            $user = User::create($data);

            // Assigner le rôle Administrateur avec le guard Sanctum
            $user->assignRole('Administrateur');

            // Créer l’enregistrement dans la table Admin
            Admin::create(['user_id' => $user->id]);

            // Mail::to($user->email)->send(new AdminCreated($user->email, $password));

            Mail::to($user->email)->queue(new AdminCreated($user->email, $password));

            return response()->json([
                'message' => 'Administrateur créé avec succès',
                'user' => $user->load('roles'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erreur création Administrateur: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Affiche un administrateur spécifique
     */
    public function show(string $id)
    {
        $user = User::with('roles')->findOrFail($id);
        return response()->json($user);
    }

    /**
     * Met à jour un administrateur
     */
    public function update(RequestUser $request, $id)
    {
        try {
            $data = $request->validated();
            Log::info('Données validées pour update Administrateur:', $data);

            $user = User::findOrFail($id);

            // Si un mot de passe est fourni, on le hash, sinon utiliser "passer123"
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                $data['password'] = Hash::make('passer123');
            }

            $user->update($data);

            // Maintenir le rôle Administrateur
            $user->syncRoles('Administrateur');

            return response()->json([
                'message' => 'Administrateur mis à jour',
                'user' => $user->load('roles'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erreur validation update Administrateur:', $e->errors());
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur serveur update Administrateur: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur serveur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprime un administrateur
     */
    public function destroy(string $id)
    {
        $this->checkSuperAdmin();

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Administrateur supprimé avec succès']);
    }

    /**
     * Active ou désactive un administrateur
     */
    public function toggleActive(string $id)
    {
        $this->checkSuperAdmin(); // Vérifie que l'utilisateur connecté est Super_Admin

        $user = User::role('Administrateur')->find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Administrateur non trouvé.'
            ], 404);
        }

        // Empêche de désactiver soi-même si nécessaire
        $superAdmin = auth('sanctum')->user();
        if ($user->id === $superAdmin->id) {
            return response()->json([
                'message' => 'Impossible de désactiver votre propre compte.'
            ], 403);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'message' => $user->is_active ? 'Administrateur activé.' : 'Administrateur désactivé.',
            'user' => $user->load('roles'),
        ]);
    }
}
