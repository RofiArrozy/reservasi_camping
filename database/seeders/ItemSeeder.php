<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = Category::pluck('id', 'name');

        // [kategori, nama, slug, deskripsi, stok, tarif/hari, biaya ganti rugi]
        $items = [
            ['Tenda', 'Tenda Dome 2P', 'tenda-dome-2p', 'Tenda dome double layer kapasitas 2 orang, ringan dan cepat dipasang.', 8, 35000, 600000],
            ['Tenda', 'Tenda Dome 4P', 'tenda-dome-4p', 'Tenda dome double layer kapasitas 4 orang, cocok untuk keluarga kecil.', 6, 50000, 900000],
            ['Perlengkapan Tidur', 'Sleeping Bag', 'sleeping-bag', 'Sleeping bag polar hangat untuk suhu dataran tinggi.', 20, 15000, 250000],
            ['Perlengkapan Tidur', 'Matras Gulung', 'matras-gulung', 'Matras gulung aluminium foil, tahan lembap dan mudah dibawa.', 20, 10000, 120000],
            ['Perlengkapan Tidur', 'Hammock', 'hammock', 'Hammock nilon parasut lengkap dengan tali pengikat.', 10, 15000, 180000],
            ['Alat Masak', 'Kompor Portable', 'kompor-portable', 'Kompor gas portable dengan casing pelindung (gas tidak termasuk).', 10, 15000, 200000],
            ['Alat Masak', 'Nesting Set', 'nesting-set', 'Satu set panci, wajan, dan peralatan makan untuk 3-4 orang.', 12, 10000, 150000],
            ['Penerangan', 'Lampu Tenda LED', 'lampu-tenda-led', 'Lampu gantung LED rechargeable untuk area tenda.', 15, 8000, 100000],
            ['Penerangan', 'Headlamp', 'headlamp', 'Headlamp LED terang dengan tiga mode cahaya.', 15, 8000, 120000],
            ['Tas & Carrier', 'Carrier 60L', 'carrier-60l', 'Carrier 60 liter dengan back system nyaman dan rain cover.', 8, 30000, 700000],
            ['Furnitur Camping', 'Meja Lipat', 'meja-lipat', 'Meja lipat aluminium ringan untuk memasak dan makan.', 6, 20000, 300000],
            ['Furnitur Camping', 'Kursi Lipat', 'kursi-lipat', 'Kursi lipat portable dengan sandaran dan tempat gelas.', 15, 10000, 150000],
            ['Aksesoris', 'Trekking Pole', 'trekking-pole', 'Tongkat trekking aluminium dengan panjang dapat diatur.', 10, 10000, 200000],
        ];

        foreach ($items as [$category, $name, $slug, $description, $stock, $rate, $replacement]) {
            Item::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id'      => $categoryIds[$category],
                    'name'             => $name,
                    'description'      => $description,
                    'total_stock'      => $stock,
                    'daily_rate'       => $rate,
                    'replacement_cost' => $replacement,
                    'is_active'        => true,
                ]
            );
        }
    }
}
