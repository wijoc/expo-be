<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ecommerce;
use App\Models\DeliveryService;

class EcommerceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Ecommerce::create(
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
        );

        DeliveryService::insert(
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
        );
    }
}
