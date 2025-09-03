<?php

namespace App\Http\Requests\Classe;

use Illuminate\Foundation\Http\FormRequest;

class ClasseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
       $rules = [
            'nom' => 'required|string|max:255',
            'niveau' => 'required|string|max:255',
            'filiere' => 'required|in:Genie Logiciel,Reseaux et systeme,Intelligence Artificielle,Ingénierie de Données,Gestion',
            'anneeacademique' => 'required|integer',
        ];

        // Si c'est une mise à jour, ignorer la classe actuelle pour le code unique
        if ($this->method() === 'PUT' || $this->method() === 'PATCH') {
            $id = $this->route('classe');
            $rules['code'] = 'required|string|unique:classes,code,'.$id;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de la classe est requis.',
            'niveau.required' => 'Le niveau de la classe est requis.',
            'filiere.required' => 'La filière de la classe est requise.',
            'filiere.in' => 'La filière doit être l\'une des suivantes : Genie Logiciel, Reseaux et systeme, Intelligence Artificielle, Ingénierie de Données, Gestion.',
            'anneeacademique.required' => 'L\'année académique est requise.',
            'anneeacademique.integer' => 'L\'année académique doit être un entier.',
        ];
    }

}
