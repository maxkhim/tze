<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->bothify('???-####')),
            'price' => fake()->randomFloat(2, 10, 5000),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'category' => fake()->randomElement(['engine', 'brakes', 'suspension', 'electrical']),
        ];
    }
}
