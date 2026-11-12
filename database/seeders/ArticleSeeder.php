<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('email', 'staff@demo.test')->firstOrFail();

        $articles = [
            [
                'title'        => '5 Tips Memilih Tenda untuk Camping Pertama Anda',
                'excerpt'      => 'Bingung memilih tenda? Perhatikan kapasitas, lapisan, bobot, ventilasi, dan kemudahan pemasangan.',
                'published_at' => now()->subDays(20),
                'content'      => <<<'TXT'
Memilih tenda adalah keputusan paling penting sebelum camping pertama. Tenda yang tepat membuat tidur nyaman dan melindungi Anda dari angin maupun hujan.

Pertama, pilih kapasitas satu orang lebih besar dari jumlah penghuni. Tenda berkapasitas 2 orang yang dipakai 2 orang biasanya terasa sempit, apalagi jika tas ikut masuk ke dalam.

Kedua, pilih tenda double layer. Lapisan luar (flysheet) menahan hujan, sedangkan lapisan dalam menjaga kehangatan dan mengurangi embun. Ketiga, perhatikan bobot dan ukuran lipat jika Anda berjalan kaki menuju lokasi.

Keempat, pastikan ada ventilasi yang cukup agar udara tetap segar. Terakhir, coba pasang tenda di rumah atau di lokasi sebelum gelap. Dengan begitu Anda tidak kebingungan saat hari sudah malam.
TXT,
            ],
            [
                'title'        => 'Checklist Perlengkapan Camping untuk Akhir Pekan',
                'excerpt'      => 'Daftar perlengkapan penting agar camping akhir pekan Anda lancar dan tidak ada yang tertinggal.',
                'published_at' => now()->subDays(12),
                'content'      => <<<'TXT'
Camping akhir pekan akan lebih menyenangkan jika persiapannya matang. Gunakan daftar berikut sebagai acuan sebelum berangkat.

Untuk tempat bermalam, siapkan tenda, sleeping bag, dan matras. Untuk memasak, bawa kompor portable, gas, nesting, serta korek api. Jangan lupa lampu tenda dan headlamp cadangan karena malam di alam terbuka sangat gelap.

Selain itu, bawa jas hujan, jaket hangat, obat pribadi, dan kotak P3K sederhana. Siapkan juga kantong sampah agar seluruh sampah dapat dibawa pulang.

Jika Anda belum memiliki semua peralatan, menyewa adalah pilihan hemat. Anda cukup membayar sesuai lama pemakaian tanpa memikirkan biaya perawatan dan tempat penyimpanan.
TXT,
            ],
            [
                'title'        => 'Cara Merawat Alat Sewaan agar Tidak Kena Biaya Ganti Rugi',
                'excerpt'      => 'Tips menjaga alat sewaan tetap aman, bersih, dan kembali dalam kondisi baik.',
                'published_at' => now()->subDays(5),
                'content'      => <<<'TXT'
Alat camping sewaan dipakai bergantian oleh banyak orang, sehingga kondisinya perlu dijaga bersama. Dengan perawatan sederhana, Anda terhindar dari biaya ganti rugi.

Periksa seluruh alat saat pengambilan. Pastikan jumlahnya sesuai dan tidak ada bagian yang sudah rusak. Laporkan kekurangan kepada staf sebelum alat dibawa pulang agar tercatat dengan jelas.

Selama dipakai, hindari menjemur tenda terlalu dekat api, jangan menarik resleting dengan paksa, dan jauhkan kompor dari bahan yang mudah terbakar. Bersihkan alat dari lumpur dan sisa makanan sebelum dikembalikan.

Jika ada alat yang rusak atau hilang, penyewa wajib mengganti sesuai nilai penggantian yang tertera pada daftar alat. Kejujuran sejak awal membuat proses penyelesaian lebih cepat dan adil bagi semua pihak.
TXT,
            ],
        ];

        foreach ($articles as $data) {
            Article::updateOrCreate(
                ['slug' => Str::slug($data['title'])],
                $data + ['user_id' => $author->id, 'status' => Article::STATUS_PUBLISHED]
            );
        }
    }
}
