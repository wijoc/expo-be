<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\models\Home;

class HomeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tb_home')->insert([
            'banner' => 'Hadir sebagai sarana bagi pelaku UMKM dan UKM di masa pandemi.',
            'about' => 'UMKM Virutal Expo hadir sebagai sarana bagi pelaku baik UMKM ataupun UKM yang terdampak pada masa pandemi. Bertujuan untuk mendukung berkembangnya UMKM dan UKM dalam negeri, sekaligus untuk mendukung terciptanya masyarakat koperasi, UMKM, dan UKM yang tangguh, mandiri, dan kompeten.
                        Dilaksanakan selama 30 hari. Mulai 1 Januari 2021 sampai dengan 31 Januari 2021.',
            'phone' => '+62xxxxxx',
            'email' => 'email@xxxx.com',
            'address' => 'Jl. Lokasi, Kota XXX, Prov. XXX, Indonesia'
        ]);
    }
}
