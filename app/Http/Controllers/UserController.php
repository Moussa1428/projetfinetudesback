<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RequestUser;
use App\Mail\Assistan;
use App\Models\Assistant;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    private function checkAdmin()
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            // Ceci déclenchera le handler unauthenticated
            throw new \Illuminate\Auth\AuthenticationException(
                'Pas accès à se connecter. Token requis.'
            );
        }

        if (!$user->hasRole('Administrateur')) {
            return response()->json([
                'message' => 'Accès refusé. Rôle Administrateur requis.'
            ], 403);
        }
    }

    public function index()
    {
        $this->checkAdmin();

        $adminId = auth()->user()->admin->id;

        $assistants = User::role('Assistant')
            ->whereHas('assistant', function ($query) use ($adminId) {
                $query->where('admin_id', $adminId);
            })
            ->with('roles')
            ->get();

        return response()->json($assistants, 200);
    }

    public function store(RequestUser $request)
    {
        $this->checkAdmin();
        try {
            $data = $request->validated();
            Log::info('Data reçue:', $data);

            $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
            // $password = 'passer123';
            $data['password'] = Hash::make($password);
            $user = User::create($data);
            $user->assignRole('Assistant');

            Assistant::create([
                'user_id' => $user->id,
                'admin_id' => auth()->user()->admin->id
            ]);

            Mail::to($user->email)->queue(new Assistan($user->email, $password));
            return response()->json([
                'message' => 'Assistant créé avec succès',
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
        $this->checkAdmin();

        $adminId = auth()->user()->admin->id;

        $user = User::role('Assistant')
            ->whereHas('assistant', function ($query) use ($adminId) {
                $query->where('admin_id', $adminId);
            })
            ->with('roles')
            ->findOrFail($id);

        return response()->json($user);
    }
    public function update(RequestUser $request, $id)
    {
        try {

            $this->checkAdmin();

            $adminId = auth()->user()->admin->id;

            $user = User::role('Assistant')
                ->whereHas('assistant', function ($query) use ($adminId) {
                    $query->where('admin_id', $adminId);
                })
                ->findOrFail($id);


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

    public function destroy($id)
    {
        $this->checkAdmin();

        $adminId = auth()->user()->admin->id;

        $user = User::role('Assistant')
            ->whereHas('assistant', function ($query) use ($adminId) {
                $query->where('admin_id', $adminId);
            })
            ->findOrFail($id);

        $user->delete();

        return response()->json(['message' => 'Assistant supprimé avec succès']);
    }

    public function toggleAssistant(string $id)
    {
        $this->checkAdmin(); // Vérifie que l'utilisateur connecté est Administrateur

        $user = User::role('Assistant')->find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Assistant non trouvé.'
            ], 404);
        }

        // Empêche de désactiver soi-même si nécessaire (optionnel)
        $currentUser = auth('sanctum')->user();
        if ($user->id === $currentUser->id) {
            return response()->json([
                'message' => 'Impossible de désactiver votre propre compte.'
            ], 403);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'message' => $user->is_active ? 'Assistant activé.' : 'Assistant désactivé.',
            'user' => $user->load('roles'),
        ]);
    }

    public function responsables()
    {
        $user = auth()->user();

        // Cas Administrateur => retourne ses assistants (users)
        if ($user->hasRole('Administrateur')) {
            $assistants = User::whereHas('assistant', function ($q) use ($user) {
                $q->where('admin_id', $user->id);
            })->get(['id', 'name', 'last_name', 'email', 'phone']);

            return response()->json($assistants);
        }

        // Cas Assistant => retourne son administrateur (user)
        if ($user->hasRole('Assistant')) {
            $assistant = $user->assistant()->with('admin.user')->first();

            if ($assistant && $assistant->admin && $assistant->admin->user) {
                $adminUser = $assistant->admin->user;
                return response()->json([[
                    'id'    => $adminUser->id,
                    'name'  => $adminUser->name,
                    'last_name' => $adminUser->last_name,
                    'email' => $adminUser->email,
                    'phone' => $adminUser->phone,
                ]]);
            }

            return response()->json([]);
        }

        // Autres rôles => retourne vide
        return response()->json([]);
    }
}
