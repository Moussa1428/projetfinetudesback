<?php

namespace App\Http\Requests\Etudiant;

use Illuminate\Foundation\Http\FormRequest;

class EtudiantRequest extends FormRequest
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
        $etudiantId = $this->route('etudiant');

        return [
            'classe_id' => 'required|exists:classes,id',
        ];
    }
    public function messages(): array
    {
        return [
            'classe_id.exists' => "La classe spécifiée n'existe pas.",
        ];
    }
}
