<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;

class RequestUser extends FormRequest
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
        $userId = $this->route('id'); // Get the user ID from the route if available
        return [
            'name'       => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'     => $userId
                ? 'required|email|unique:users,email,' . $userId
                : 'required|email|unique:users,email',
            'phone'      => $userId
                ? 'required|string|max:20|unique:users,phone,' . $userId
                : 'required|string|max:20|unique:users,phone',
            'address'    => 'nullable|string|max:255',
            'is_active'  => 'boolean',
            'profile_picture' => 'nullable|string',
        ];
    }
    public function messages(): array
    {
        $userId = $this->route('id'); // pour update

        return [
            'name.required'       => "Le prénom est obligatoire.",
            'name.string'         => "Le prénom doit être une chaîne de caractères.",
            'name.max'            => "Le prénom ne peut pas dépasser 255 caractères.",

            'last_name.required'  => "Le nom est obligatoire.",
            'last_name.string'    => "Le nom doit être une chaîne de caractères.",
            'last_name.max'       => "Le nom ne peut pas dépasser 255 caractères.",

            'email.required'      => "L'email est obligatoire.",
            'email.email'         => "L'email doit être une adresse email valide.",
            'email.unique'        => "Cet email est déjà utilisé.",

            'phone.required'      => "Le numéro de téléphone est obligatoire.",
            'phone.string'        => "Le numéro de téléphone doit être une chaîne de caractères.",
            'phone.max'           => "Le numéro de téléphone ne peut pas dépasser 20 caractères.",
            'phone.unique'        => "Ce numéro de téléphone est déjà utilisé.",

            'address.max'         => "L'adresse ne peut pas dépasser 255 caractères.",
        ];
    }
}
