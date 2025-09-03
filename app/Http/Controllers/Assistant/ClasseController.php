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
        $classe = Classe::create($request->validated());
        return response()->json($classe, 201);
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
}
