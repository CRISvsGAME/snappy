<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Store>
 */
class StoreFactory extends Factory
{
    protected $model = Store::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company,
            // Generate coordinates within approximate UK bounds.
            'lat'  => $this->faker->latitude(49.9, 58.6),
            'long' => $this->faker->longitude(-8.6, 1.8),
            'is_open' => $this->faker->boolean,
            'store_type' => $this->faker->randomElement(['Takeaway', 'Shop', 'Restaurant', 'Cafe', 'Bar', 'Pub', 'Supermarket', 'Convenience Store', 'Off Licence', 'Grocery Store']),
            'max_delivery_distance' => $this->faker->numberBetween(1, 100),
        ];
    }
}
