<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'store_name' => 'Store '.$this->faker->unique()->numerify("#####"),
            'domain' => $this->faker->bothify('?????-#####'),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->unique()->numerify('08##########'),
            'whatsapp' => $this->faker->unique()->numerify('08##########'),
            // 'description' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Dolorem, cupiditate?',
            'full_address' => $this->faker->address(),
            'category_id' => $this->faker->numberBetween(1, 14),
            // 'user_id' => $this->faker->numberBetween(2, 3),
            'user_id' => '1',
            'province_id' => $this->faker->numberBetween(1, 33),
            'city_id' => $this->faker->numberBetween(1, 500),
            'district_id' => $this->faker->numberBetween(1, 7011)
        ];
    }
}
