<?php

namespace App\Http\Controllers\Enseignant;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestUser;
use App\Mail\EnseignantCreated;
use App\Models\Enseignant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnseignantController extends Controller
{
    private function checkAccess()
    {
        $user = auth('sanctum')->user();
        if (!$user || !$user->hasAnyRole(['Administrateur', 'Assistant'])) {
            abort(403, 'Accès refusé. Rôle Administrateur ou Assistant requis.');
        }
    }

    public function index()
    {
        $this->checkAccess();
        $enseignants = User::role('Enseignant')->with('roles')->get();
        return response()->json($enseignants);
    }

    public function store(RequestUser $request)
    {
        $this->checkAccess();

        try {
            $data = $request->validated();
            Log::info('Données reçues pour création Enseignant:', $data);

            // Vérifie si l'enseignant existe déjà par email ou téléphone
            $existingUser = User::where('email', $data['email'])
                ->orWhere('phone', $data['phone'])
                ->first();
            if ($existingUser) {
                return response()->json([
                    'message' => 'Un enseignant avec cet email ou téléphone existe déjà.'
                ], 409); // 409 = Conflict
            }

            // Générer un mot de passe aléatoire si non fourni
            $password = $data['password'] ?? substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
            // $password = 'passer123';
            $data['password'] = Hash::make($password);

            $user = User::create($data);
            $user->assignRole('Enseignant');

            Enseignant::create(
                ['user_id' => $user->id]
            );

            // Envoi de l'email
            Mail::to($user->email)->queue(new EnseignantCreated($user->email, $password));

            return response()->json([
                'message' => 'Enseignant créé avec succès',
                'user' => $user->load('roles'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erreur création Enseignant: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function show($id)
    {
        $this->checkAccess();
        $user = User::role('Enseignant')->with('roles')->findOrFail($id);
        return response()->json($user);
    }

    public function update(RequestUser $request, $id)
    {
        $this->checkAccess();
        try {
            $data = $request->validated();
            $user = User::role('Enseignant')->findOrFail($id);

            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user->update($data);

            return response()->json([
                'message' => 'Enseignant mis à jour',
                'user' => $user->load('roles'),
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour Enseignant: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $this->checkAccess();
        $user = User::role('Enseignant')->findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Enseignant supprimé avec succès']);
    }

    public function toggleActive($id)
    {
        $this->checkAccess();
        $user = User::role('Enseignant')->findOrFail($id);

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'message' => $user->is_active ? 'Enseignant activé.' : 'Enseignant désactivé.',
            'user' => $user->load('roles'),
        ]);
    }
}
