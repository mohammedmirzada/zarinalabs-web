<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Training Centre',
            'address' => fake()->streetAddress(),
            'city' => fake()->randomElement(array_keys(config('options.cities'))),
        ];
    }
}
