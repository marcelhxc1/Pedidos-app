<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'unit_price' => $this->faker->randomFloat(2, 1, 1000),
            'description' => $this->faker->sentence,
            'available_quantity' => $this->faker->numberBetween(1, 100),
            'SKU' => $this->faker->unique()->bothify('SKU-####'),
        ];
    }
}

