<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'product_uuid' => Str::uuid(),
            'name' => 'Product '.$this->faker->unique()->numerify("#####"),
            'condition' => $this->faker->randomElement(['N', 'SH']),
            'initial_price' => $this->faker->randomFloat(2),
            'net_price' => $this->faker->randomFloat(2),
            'disc_percent' => $this->faker->randomFloat(2, 0, 99),
            'disc_price' => $this->faker->randomFloat(2),
            'weight_g' => $this->faker->numberBetween(1, 2000),
            'min_purchase' => $this->faker->numberBetween(1, 10),
            'store_id' => $this->faker->numberBetween(1, 200),
            'category_id' => $this->faker->numberBetween(1, 3)
        ];
    }
}
