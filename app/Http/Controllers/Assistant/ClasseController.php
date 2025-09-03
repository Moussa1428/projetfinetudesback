<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Classe\ClasseRequest;
use App\Models\Classe;
use Illuminate\Http\Request;

class ClasseController extends Controller
{
    public function index()
    {
        return response()->json(Classe::all(), 200);
    }

    public function store(ClasseRequest $request)
    {
        $data = $request->validated();

        // Générer un code unique pour la classe
        $data['code'] = $this->generateUniqueCode($data['filiere'], $data['niveau'], $data['anneeacademique']);

        // Vérifier unicité du code (optionnel, pour être sûr)
        $suffix = 1;
        $original_code = $data['code'];
        while (Classe::where('code', $data['code'])->exists()) {
            $data['code'] = $original_code . '-' . $suffix;
            $suffix++;
        }

        $classe = Classe::create($data);

        return response()->json($classe, 201);
    }
    private function getFiliereCode($filiere)
    {
        $filiereCodes = [
            'Genie Logiciel' => 'GL',
            'Reseaux et Systeme' => 'RS',
            'Intelligence Artificielle' => 'IA',
            'Ingénierie de Données' => 'Data Science',
            'Informatique Appliquée a la Gestion des Entreprises' => 'IAGE',
        ];

        return $filiereCodes[$filiere] ?? strtoupper(substr(str_replace(' ', '', $filiere), 0, 2)); // Fallback si la filière n'est pas dans la liste
    }

    private function generateUniqueCode($filiere, $niveau, $anneeacademique)
    {
        $filiereCode = $this->getFiliereCode($filiere);
        $baseCode = "{$filiereCode}-{$niveau}-{$anneeacademique}"; // Exemple : GL-L1-2023

        $code = $baseCode;
        $counter = 1;

        // Vérifier si le code existe déjà
        while (Classe::where('code', $code)->exists()) {
            $code = "{$baseCode}-{$counter}"; // Ajoute un suffixe si nécessaire, ex : GL-L1-2023-1
            $counter++;
        }

        return $code;
    }

    public function update(ClasseRequest $request, $id)
    {
        $classe = Classe::findOrFail($id);
        $classe->update($request->validated());
        return response()->json($classe, 200);
    }

    public function destroy($id)
    {
        $classe = Classe::find($id);
        if (!$classe) {
            return response()->json(['message' => 'Classe non trouvée'], 404);
        }

        $classe->delete();
        return response()->json(['message' => 'Classe supprimée avec succès'], 200);
    }

    public function show($id)
    {
        // Récupérer la classe avec ses étudiants (optionnel)
        $classe = Classe::with('etudiants')->find($id);
        // $classe = Classe::find($id);

        if (!$classe) {
            return response()->json(['message' => 'Classe non trouvée'], 404);
        }

        return response()->json($classe, 200);
    }
}
