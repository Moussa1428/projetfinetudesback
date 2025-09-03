<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends FormRequest
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
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string|max:255',
            'is_active'  => 'boolean',
            'profile_picture' => 'nullable|string',
        ];
    }
    public function messages(): array
    {
        return [
            'email.unique' => "Cet email est déjà utilisé.",
            'password.min' => "Le mot de passe doit contenir au moins 8 caractères.",
        ];
    }
}
