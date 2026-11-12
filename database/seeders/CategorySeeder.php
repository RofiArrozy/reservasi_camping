<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Tenda'               => 'Tenda berbagai kapasitas untuk camping solo hingga keluarga.',
            'Perlengkapan Tidur'  => 'Sleeping bag, matras, dan hammock agar tidur nyaman di alam.',
            'Alat Masak'          => 'Kompor dan peralatan masak portable untuk masak di lokasi.',
            'Penerangan'          => 'Lampu tenda dan headlamp untuk aktivitas malam hari.',
            'Tas & Carrier'       => 'Carrier dan tas gunung untuk membawa perlengkapan.',
            'Furnitur Camping'    => 'Meja dan kursi lipat agar area camp lebih nyaman.',
            'Aksesoris'           => 'Perlengkapan pendukung seperti trekking pole.',
        ];

        foreach ($categories as $name => $description) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $description]
            );
        }
    }
}
