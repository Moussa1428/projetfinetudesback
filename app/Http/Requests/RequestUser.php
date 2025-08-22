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
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string|max:255',
            'is_active'  => 'boolean',
            'profile_picture' => 'nullable|string',
        ];
    }
}
