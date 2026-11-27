<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dibatasi oleh middleware role:admin pada route
    }

    /** Email disimpan huruf kecil agar tidak ada akun ganda akibat beda kapitalisasi. */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => Str::lower(trim($this->input('email')))]);
        }
    }

    public function rules(): array
    {
        /** @var User|null $user  Terisi saat edit (route model binding) */
        $user     = $this->route('user');
        $isCreate = ! $user;

        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone'     => ['nullable', 'string', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/'],
            'address'   => ['nullable', 'string', 'max:500'],
            'role'      => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_STAFF, User::ROLE_USER])],
            'is_active' => ['nullable', 'boolean'],
            // Wajib saat membuat akun; saat edit boleh kosong (password tidak diubah).
            'password'  => [$isCreate ? 'required' : 'nullable', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'required'       => ':attribute wajib diisi.',
            'email'          => 'Format :attribute tidak valid.',
            'unique'         => ':attribute sudah terdaftar.',
            'max'            => ':attribute terlalu panjang (maksimal :max).',
            'min'            => ':attribute minimal :min karakter.',
            'confirmed'      => 'Konfirmasi :attribute tidak cocok.',
            'role.in'        => 'Peran tidak valid.',
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
            'address'  => 'Alamat',
            'role'     => 'Peran',
            'password' => 'Password',
        ];
    }
}
