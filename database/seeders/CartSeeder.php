<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cart;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Cart::insert([
            [
                'product_uuid' => '99791f3b-8288-4ece-a7fa-bb87a34eb8e3',
                'product_qty' => 3,
                'note' => 'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Provident, voluptas.',
                'user_id' => 1,
                'store_id' => 1,
                'created_at' => now(),
                'created_tz' => date_default_timezone_get(),
                'updated_at' => now(),
                'updated_tz' => date_default_timezone_get()
            ],
            [
                'product_uuid' => '2629f843-13ac-46a7-ad46-2e69152d2053',
                'product_qty' => 1,
                'note' => '2 Lorem, ipsum dolor sit amet consectetur adipisicing elit. Provident, voluptas.',
                'user_id' => 1,
                'store_id' => 2,
                'created_at' => now(),
                'created_tz' => date_default_timezone_get(),
                'updated_at' => now(),
                'updated_tz' => date_default_timezone_get()
            ],
            [
                'product_uuid' => '550e42e0-bb78-4455-b533-b5ea5fbf6883',
                'product_qty' => 2,
                'note' => '3 Lorem, ipsum dolor sit amet consectetur adipisicing elit. Provident, voluptas.',
                'user_id' => 1,
                'store_id' => 2,
                'created_at' => now(),
                'created_tz' => date_default_timezone_get(),
                'updated_at' => now(),
                'updated_tz' => date_default_timezone_get()
            ]
        ]);
    }
}
