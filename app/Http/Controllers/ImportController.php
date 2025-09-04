<?php

namespace App\Http\Controllers;

use App\Imports\EtudiantsImport;
use App\Imports\EtudiantsImportCreatedClasse;
use App\Models\Classe;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function importEtudiant(Request $request)
    {
        $request->validate([
            'classe_id' => 'required|exists:classes,id',
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $classe = Classe::findOrFail($request->classe_id);

        Excel::import(new EtudiantsImport($classe), $request->file('file'));

        return response()->json([
            'message' => 'Import des étudiants terminé avec succès.'
        ]);
    }
    public function importEtudiantCreationClasse(Request $request)
    {
        $request->validate([
            'file' => 'nullable|file|mimes:xlsx,xls',
            'nom' => 'required_without:classe_id|string|max:255',
            'niveau' => 'required_without:classe_id|string|max:255',
            'filiere' => 'required_without:classe_id|string|max:255',
            'anneeacademique' => 'required_without:classe_id|string|max:255',
        ]);

        if ($request->classe_id) {
            $classe = Classe::findOrFail($request->classe_id);
        } else {
            $assistant = auth()->user()->assistant; // récupère le record Assistant lié à l'utilisateur connecté

            if (!$assistant || !$assistant->admin) {
                return response()->json(['message' => 'Cet assistant n’a pas d’administrateur lié.'], 422);
            }

            $admin = $assistant->admin; // récupère l'administrateur lié

            // Créer la classe
            $classe = Classe::create([
                'nom' => $request->nom,
                'niveau' => $request->niveau,
                'filiere' => $request->filiere,
                'anneeacademique' => $request->anneeacademique,
                'created_by' => $assistant->user_id,   // id de l’assistant
                'responsable_id' => $admin->user_id,   // id du user admin lié
                'code' => $request->filiere . '-' . $request->niveau . '-' . $request->anneeacademique,
            ]);
        }

        // Si un fichier Excel est fourni, importer les étudiants
        if ($request->hasFile('file')) {
            Excel::import(new EtudiantsImportCreatedClasse($classe), $request->file('file'));
        }

        return response()->json([
            'message' => 'Classe créée avec succès et import d’étudiants terminé si fichier fourni.',
            'classe' => $classe
        ]);
    }
}
