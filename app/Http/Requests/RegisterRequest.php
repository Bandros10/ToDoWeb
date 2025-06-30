<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255|regex:/^[\pL\s\-]+$/u', // Hanya huruf dan spasi
            'email' => 'required|email|unique:users,email', // Validasi DNS email
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised() // Cek kebocoran data (require package laravel/helpers)
            ]
        ];
    }

    public function messages()
    {
        return [
            'name.regex' => 'Nama hanya boleh mengandung huruf dan spasi',
            'email.email' => 'Format email tidak valid',
            'password.uncompromised' => 'Password terdeteksi tidak aman (sudah pernah bocor)'
        ];
    }
}
