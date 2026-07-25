<?php

namespace App\Http\Requests\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:80'],
            'prenom' => ['required', 'string', 'max:80'],
            'sexe' => ['required', 'string', 'in:homme,femme,autre'],
            'telephone' => ['required', 'string', 'max:20', 'unique:'.User::class.',telephone'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:190', 'unique:'.User::class.',email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'device_name' => ['nullable', 'string', 'max:120'],
        ];
    }
}
