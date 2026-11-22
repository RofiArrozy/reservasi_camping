<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('user.profile.edit', ['user' => $request->user()]);
    }

    /** Perbarui data pribadi. Role & status aktif tidak bisa diubah dari sini. */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'   => ['required', 'string', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'required'    => ':attribute wajib diisi.',
            'email'       => 'Format :attribute tidak valid.',
            'unique'      => ':attribute sudah dipakai akun lain.',
            'max'         => ':attribute maksimal :max karakter.',
            'phone.regex' => 'Format nomor telepon tidak valid (contoh: 081234567890).',
        ], [
            'name'    => 'Nama',
            'email'   => 'Email',
            'phone'   => 'Nomor telepon',
            'address' => 'Alamat',
        ]);

        $user->update($data);

        return redirect()->route('user.profile.edit')->with('success', 'Data pribadi berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
        ], [
            'required'                 => ':attribute wajib diisi.',
            'current_password.current_password' => 'Password saat ini salah.',
            'min'                      => ':attribute minimal :min karakter.',
            'confirmed'                => 'Konfirmasi password baru tidak cocok.',
            'password.regex'           => 'Password harus mengandung huruf dan angka.',
        ], [
            'current_password' => 'Password saat ini',
            'password'         => 'Password baru',
        ]);

        // Di-hash otomatis oleh cast 'hashed' pada model User.
        $request->user()->update(['password' => $data['password']]);

        return redirect()->route('user.profile.edit')->with('success', 'Password berhasil diubah.');
    }
}
