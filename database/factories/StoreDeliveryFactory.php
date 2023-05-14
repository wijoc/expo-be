<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StoreDeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'store_id' => $this->faker->numberBetween(2, 200),
            // 'store_id' => '1',
            'delivery_courier_id' => $this->faker->numberBetween(1, 3)
        ];
    }
}
