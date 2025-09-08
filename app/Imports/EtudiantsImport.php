<?php

namespace App\Imports;

use App\Mail\CreationEtudiantMail;
use App\Models\Classe;
use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EtudiantsImport implements ToCollection, WithHeadingRow
{
    protected $classe;

    public function __construct(Classe $classe)
    {
        $this->classe = $classe;
    }

    public function collection(Collection $rows)
    {
        // Récupérer le dernier étudiant de la classe pour continuer le matricule
        $dernierEtudiant = Etudiant::where('classe_id', $this->classe->id)
            ->orderBy('id', 'desc')
            ->first();

        // Compteur global pour la classe
        $numeroClasse = $dernierEtudiant
            ? ((int) explode('-', $dernierEtudiant->matricule)[2]) + 1
            : 1;

        // Compteur global pour tous les étudiants
        $totalEtudiants = Etudiant::count() + 1;

        foreach ($rows as $row) {
            try {
                // Nettoyage des données
                $name = trim($row['name'] ?? '');
                $last_name = trim($row['last_name'] ?? '');
                $email = strtolower(trim($row['email'] ?? ''));
                $phone = $row['phone'] ?? null;
                $address = $row['address'] ?? null;

                // Vérifier les champs obligatoires
                if (empty($name) || empty($last_name) || empty($email)) {
                    continue;
                }

                // Vérifier si email existe déjà
                if (User::where('email', $email)->exists()) continue;

                // Générer mot de passe aléatoire
                $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);


                // Créer l'utilisateur
                $user = User::create([
                    'name' => $name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                    'password' => Hash::make($password),
                ]);

                $user->assignRole('Etudiant');

                // Générer matricule : compteur global + année académique + compteur dans la classe
                $matricule = $totalEtudiants . '-' . $this->classe->anneeacademique . '-' . $numeroClasse . '/ISI';

                // Créer l'étudiant
                Etudiant::create([
                    'user_id' => $user->id,
                    'classe_id' => $this->classe->id,
                    'matricule' => $matricule,
                ]);

                // Envoyer email en queue
                Mail::to($user->email)->queue(new CreationEtudiantMail($name, $email, $password));

                // Incrémenter les compteurs
                $numeroClasse++;
                $totalEtudiants++;

            } catch (\Exception $e) {
                Log::error('Erreur import étudiant : ' . $e->getMessage());
                continue;
            }
        }
    }
}
