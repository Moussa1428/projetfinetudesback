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
}
