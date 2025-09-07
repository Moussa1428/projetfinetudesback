<?php

namespace App\Imports;

use App\Mail\CreationEtudiantMail;
use App\Models\Classe;
use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EtudiantsImportCreatedClasse implements ToCollection, WithHeadingRow
{
    protected $classe;

    /**
     * Constructeur avec la classe
     */
    public function __construct(Classe $classe)
    {
        $this->classe = $classe;
    }

    /**
     * Traitement de chaque ligne du fichier Excel
     *
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            // Vérifier les champs obligatoires
            if (empty($row['name']) || empty($row['last_name']) || empty($row['email'])) {
                continue; // ignorer les lignes invalides
            }

            // Vérifier si l'email existe déjà
            if (User::where('email', $row['email'])->exists()) {
                continue; // ignorer l'utilisateur déjà existant
            }

            // Générer mot de passe aléatoire
            $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
            // $password = 'passer123'; // Pour simplifier les tests, on utilise un mot de passe fixe
            // Créer l'utilisateur
            $user = User::create([
                'name' => $row['name'],
                'last_name' => $row['last_name'],
                'email' => $row['email'],
                'phone' => $row['phone'] ?? null,
                'address' => $row['address'] ?? null,
                'password' => Hash::make($password),
            ]);

            // Assigner le rôle Etudiant
            $user->assignRole('Etudiant');

            // Générer matricule continu dans la classe
            $dernierEtudiant = Etudiant::where('classe_id', $this->classe->id)
                ->orderBy('id', 'desc')
                ->first();

            $numeroClasse = $dernierEtudiant
                ? ((int) explode('-', $dernierEtudiant->matricule)[2]) + 1
                : 1;

            $totalEtudiants = Etudiant::count() + 1;
            $matricule = $totalEtudiants . '-' . $this->classe->anneeacademique . '-' . $numeroClasse . '/ISI';

            // Créer l'étudiant lié à la classe
            Etudiant::create([
                'user_id' => $user->id,
                'classe_id' => $this->classe->id,
                'matricule' => $matricule,
            ]);

            // Envoyer email via queue
            Mail::to($user->email)->queue(new CreationEtudiantMail($user->name, $user->email, $password));
        }
    }
}
