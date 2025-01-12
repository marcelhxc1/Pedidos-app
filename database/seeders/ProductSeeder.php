<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::create([
            'name' => 'Produto 1',
            'unit_price' => 99.99,
            'description' => 'Descrição do Produto 1',
            'available_quantity' => 100
        ]);

        Product::create([
            'name' => 'Produto 2',
            'unit_price' => 49.99,
            'description' => 'Descrição do Produto 2',
            'available_quantity' => 100
        ]);

        Product::create([
            'name' => 'Produto 3',
            'unit_price' => 79.99,
            'description' => 'Descrição do Produto 3',
            'available_quantity' => 100
        ]);
    }
}
