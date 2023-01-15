<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StoreCategory;
use App\Models\ProductCategory;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        StoreCategory::create(
            [
                'name' => 'Barang'
            ],
            [
                'name' => 'Jasa'
            ],
            [
                'name' => 'Kuliner'
            ],
            [
                'name' => 'Pendidikan'
            ],
            [
                'name' => 'Fashion / Pakaian'
            ],
            [
                'name' => 'Pertanian'
            ],
            [
                'name' => 'Peternakan'
            ],
            [
                'name' => 'Otomotif'
            ],
            [
                'name' => 'Agrobisnis'
            ],
            [
                'name' => 'Produk Kreatif'
            ],
            [
                'name' => 'Teknologi Digital'
            ],
            [
                'name' => 'Teknologi Internet'
            ],
            [
                'name' => 'Kebutuhan Keluarga'
            ],
            [
                'name' => 'Kecantikan'
            ]
        );

        ProductCategory::create(
            [
                'id' => 1,
                'name' => 'Barang',
                'is_sub_category' => 0,
                'parent_id' => null
            ],
            [
                'id' => 2,
                'name' => 'Jasa',
                'is_sub_category' => 0,
                'parent_id' => null
            ],
            [
                'id' => 3,
                'name' => 'Fashion',
                'is_sub_category' => 0,
                'parent_id' => null
            ],
            [
                'id' => 4,
                'name' => 'Rumah Tangga',
                'is_sub_category' => 0,
                'parent_id' => null
            ],
            [
                'id' => 5,
                'name' => 'Fashion',
                'is_sub_category' => 0,
                'parent_id' => null
            ],
            [
                'id' => 6,
                'name' => 'Fashion Pria',
                'is_sub_category' => 1,
                'parent_id' => 3
            ],
            [
                'id' => 7,
                'name' => 'Fashion Wanita',
                'is_sub_category' => 1,
                'parent_id' => 3
            ],
            [
                'id' => 8,
                'name' => 'Fashion Anak - Anak',
                'is_sub_category' => '1',
                'parent_id' => 3
            ]
        );
    }
}
