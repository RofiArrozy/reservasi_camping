<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /** Maksimal percobaan gagal sebelum dikunci sementara. */
    private const MAX_ATTEMPTS = 5;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'email'    => 'Format :attribute tidak valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'email'    => 'Email',
            'password' => 'Password',
        ];
    }

    /**
     * Coba login. Gagal jika kredensial salah, akun nonaktif,
     * atau terlalu banyak percobaan.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            // Pesan sengaja umum agar tidak membocorkan email mana yang terdaftar.
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        // Kredensial benar, tetapi akun dinonaktifkan oleh admin.
        if (! Auth::user()->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Akun Anda dinonaktifkan. Silakan hubungi admin.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    /** Kunci pembatasan: kombinasi email + alamat IP. */
    private function throttleKey(): string
    {
        return Str::transliterate(Str::lower((string) $this->input('email')) . '|' . $this->ip());
    }
}
