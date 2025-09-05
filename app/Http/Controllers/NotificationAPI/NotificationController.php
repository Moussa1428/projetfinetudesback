<?php

namespace App\Http\Controllers\NotificationAPI;

use App\Events\NotificationSent;
use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Models\Groupe;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{
    /**
     * Envoi d’une notification
     */
    public function send(Request $request)
    {
        $request->validate([
            'target_type' => 'required|string|in:Classe,Groupe,Enseignant,Assistant,Administrateur',
            'target_id'   => 'nullable|integer',
            'message'     => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        // Création de la notification
        $notification = Notification::create([
            'sender_id'   => $user->id,
            'target_type' => $request->target_type,
            'target_id'   => $request->target_id,
            'message'     => $request->message,
        ]);

        // Diffusion via Pusher
        broadcast(new NotificationSent($notification))->toOthers();

        // Si c’est un Admin → envoyer aussi par email
        if ($user->hasRole('Administrateur')) {
            $this->sendEmailNotification($notification);
        }

        return response()->json([
            'message' => 'Notification envoyée',
            'data'    => $notification,
        ]);
    }

    /**
     * Envoi email quand Admin crée une notification
     */
    private function sendEmailNotification(Notification $notification)
    {
        $users = collect();

        switch ($notification->target_type) {
            case 'Assistant':
            case 'Enseignant':
            case 'Administrateur':
                $users = collect([User::find($notification->target_id)]);
                break;

            case 'Classe':
                $users = Etudiant::where('classe_id', $notification->target_id)
                    ->with('user')->get()->pluck('user');
                break;

            case 'Groupe':
                $users = Groupe::find($notification->target_id)
                    ?->membres()->with('user')->get()->pluck('user') ?? collect();
                break;
        }

        foreach ($users as $user) {
            if ($user && $user->email) {
                Mail::to($user->email)
                    ->queue(new \App\Mail\NotificationMail($notification));
            }
        }
    }

    /**
     * 📌 Récupérer les notifications
     */
    public function getNotifications(Request $request)
    {
        $request->validate([
            'target_type' => 'nullable|string|in:Classe,Groupe,Enseignant,Assistant,Administrateur',
            'target_id'   => 'nullable|integer',
        ]);

        $user        = Auth::user();
        $target_type = $request->target_type;
        $target_id   = $request->target_id;

        // ---------------------------
        // Étudiant
        // ---------------------------
        if ($user->hasRole('Etudiant')) {
            $classeId = $user->etudiant->classe_id;

            $notifications = Notification::where(function ($q) use ($classeId) {
                $q->where('target_type', 'Classe')
                    ->where('target_id', $classeId);
            })
                ->orderByDesc('created_at')
                ->get();
        }

        // ---------------------------
        // Assistant / Enseignant / Administrateur
        // ---------------------------
        else {
            $notifications = Notification::query();

            if ($target_type && $target_id) {
                $notifications->where(function ($q) use ($target_type, $target_id) {
                    $q->where('target_type', $target_type)
                        ->where('target_id', $target_id);
                });
            }

            $notifications = $notifications->orderByDesc('created_at')->get();
        }

        // ---------------------------
        // Marquer comme lu
        // ---------------------------
        foreach ($notifications as $notif) {
            $readBy = $notif->read_by ?? [];
            $isRecipient = false;

            // Étudiant
            if ($user->hasRole('Etudiant')) {
                if ($notif->target_type === 'Classe' && $notif->target_id === $user->etudiant->classe_id) {
                    $isRecipient = true;
                }
            }
            // Assistant / Enseignant / Administrateur
            else {
                if (
                    in_array($notif->target_type, ['Enseignant', 'Assistant', 'Administrateur'])
                    && $notif->target_id === $user->id
                ) {
                    $isRecipient = true;
                } elseif ($notif->target_type === 'Classe') {
                    // Selon ta logique, tu peux vérifier si user est responsable/membre
                    $isRecipient = true;
                } elseif ($notif->target_type === 'Groupe') {
                    $isRecipient = $user->groupes->pluck('id')->contains($notif->target_id);
                }
            }

            if ($isRecipient && !in_array($user->id, $readBy)) {
                $readBy[] = $user->id;
                $notif->update(['read_by' => $readBy]);
            }
        }

        return response()->json($notifications);
    }
}
