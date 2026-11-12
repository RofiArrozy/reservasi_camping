<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $items = Item::all()->keyBy('slug');

        $packages = [
            [
                'name'             => 'Paket Solo Hiking',
                'description'      => 'Perlengkapan ringkas untuk pendaki atau pecamp solo.',
                'discount_percent' => 10,
                'items'            => ['tenda-dome-2p' => 1, 'sleeping-bag' => 1, 'matras-gulung' => 1, 'headlamp' => 1, 'carrier-60l' => 1],
            ],
            [
                'name'             => 'Paket Camping Berdua',
                'description'      => 'Paket lengkap untuk camping santai berdua, termasuk alat masak.',
                'discount_percent' => 10,
                'items'            => ['tenda-dome-2p' => 1, 'sleeping-bag' => 2, 'matras-gulung' => 2, 'kompor-portable' => 1, 'nesting-set' => 1, 'lampu-tenda-led' => 1],
            ],
            [
                'name'             => 'Paket Camping Keluarga',
                'description'      => 'Paket untuk 4 orang dengan tenda besar, furnitur, dan alat masak.',
                'discount_percent' => 15,
                'items'            => ['tenda-dome-4p' => 1, 'sleeping-bag' => 4, 'matras-gulung' => 4, 'kompor-portable' => 1, 'nesting-set' => 1, 'lampu-tenda-led' => 2, 'meja-lipat' => 1, 'kursi-lipat' => 4],
            ],
        ];

        foreach ($packages as $data) {
            $package = Package::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name'             => $data['name'],
                    'description'      => $data['description'],
                    'discount_percent' => $data['discount_percent'],
                    'is_active'        => true,
                ]
            );

            $sync = [];
            foreach ($data['items'] as $slug => $quantity) {
                $sync[$items[$slug]->id] = ['quantity' => $quantity];
            }
            $package->items()->sync($sync);
        }
    }
}
