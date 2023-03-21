<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ecommerce;
use App\Models\DeliveryCourier;

class EcommerceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Ecommerce::insert([
            [
                'name' => 'Blibli'
            ],
            [
                'name' => 'Bukalapak'
            ],
            [
                'name' => 'JD.ID'
            ],
            [
                'name' => 'Lazada'
            ],
            [
                'name' => 'Shopee'
            ],
            [
                'name' => 'Tokopedia'
            ]
        ]);

        DeliveryCourier::insert([
            [
                'name' => 'JNE',
                'ro_api_param' => 'jne'
            ],
            [
                'name' => 'POS',
                'ro_api_param' => 'pos'
            ],
            [
                'name' => 'TIKI',
                'ro_api_param' => 'tiki'
            ]
        ]);
    }
}
