<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $products = [
            'Mechanical Keyboard', 'Wireless Mouse', 'Gaming Monitor',
            'Laptop Stand', 'USB-C Hub', 'Noise Cancelling Headphones',
            'Webcam 1080p', 'Microphone', 'Desk Mat', 'Ergonomic Chair'
        ];

        return [
            'user_id' => \App\Models\User::factory(),
            'name' => fake()->randomElement($products) . ' ' . fake()->word(),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10, 500),
        ];
    }
}
