<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Urutan penting: user -> kategori -> alat -> paket -> artikel -> booking
     * (booking membutuhkan user & alat yang sudah ada).
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            ItemSeeder::class,
            PackageSeeder::class,
            ArticleSeeder::class,
            BookingSeeder::class,
        ]);
    }
}
