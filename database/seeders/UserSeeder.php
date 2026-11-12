<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Tiga akun demo wajib + dua user tambahan untuk variasi data analitik.
     * Password diberikan dalam bentuk teks biasa karena model User
     * otomatis meng-hash lewat cast 'hashed'.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Administrator',  'email' => 'admin@demo.test', 'password' => 'Admin#2026', 'role' => User::ROLE_ADMIN, 'phone' => '081200000001', 'address' => 'Kantor Pusat, Surakarta'],
            ['name' => 'Staff Rental',   'email' => 'staff@demo.test', 'password' => 'Staff#2026', 'role' => User::ROLE_STAFF, 'phone' => '081200000002', 'address' => 'Gudang Alat, Surakarta'],
            ['name' => 'Demo User',      'email' => 'user@demo.test',  'password' => 'User#2026',  'role' => User::ROLE_USER,  'phone' => '081200000003', 'address' => 'Jl. Slamet Riyadi No. 10, Surakarta'],
            ['name' => 'Budi Santoso',   'email' => 'budi@demo.test',  'password' => 'User#2026',  'role' => User::ROLE_USER,  'phone' => '081200000004', 'address' => 'Jl. Veteran No. 5, Sukoharjo'],
            ['name' => 'Siti Aminah',    'email' => 'siti@demo.test',  'password' => 'User#2026',  'role' => User::ROLE_USER,  'phone' => '081200000005', 'address' => 'Jl. Ahmad Yani No. 21, Karanganyar'],
        ];

        foreach ($users as $data) {
            $user = User::firstOrNew(['email' => $data['email']]);
            $user->fill($data + ['is_active' => true]);
            $user->forceFill(['email_verified_at' => now()])->save();
        }
    }
}
