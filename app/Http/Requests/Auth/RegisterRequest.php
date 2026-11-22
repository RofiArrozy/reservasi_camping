<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalisasi email ke huruf kecil sebelum validasi. SQLite membedakan
     * huruf besar/kecil, sehingga tanpa ini "Rofi@x.com" dan "rofi@x.com"
     * dianggap dua akun berbeda.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            // Nomor telepon Indonesia: 08xx, 628xx, atau +628xx (9-13 digit setelah 8)
            'phone'    => ['required', 'string', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/'],
            // Minimal 8 karakter, wajib mengandung huruf dan angka
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'required'       => ':attribute wajib diisi.',
            'email'          => 'Format :attribute tidak valid.',
            'unique'         => ':attribute sudah terdaftar.',
            'max'            => ':attribute maksimal :max karakter.',
            'min'            => ':attribute minimal :min karakter.',
            'confirmed'      => 'Konfirmasi :attribute tidak cocok.',
            'phone.regex'    => 'Format nomor telepon tidak valid (contoh: 081234567890).',
            'password.regex' => 'Password harus mengandung huruf dan angka.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'     => 'Nama',
            'email'    => 'Email',
            'phone'    => 'Nomor telepon',
            'password' => 'Password',
        ];
    }
}
