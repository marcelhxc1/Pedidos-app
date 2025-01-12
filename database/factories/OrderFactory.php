<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        // Gerar um ou mais produtos aleatórios
        $products = Product::inRandomOrder()->limit(3)->get(); // Ajuste o limite conforme necessário

        // Criar o order_data como um array contendo produtos com id, quantidade e descrição
        $orderData = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'quantity' => $this->faker->numberBetween(1, 5),  // Quantidade entre 1 e 5
                'description' => $product->description,  // Pega a descrição do produto
            ];
        });

        return [
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'canceled']),
            'total_value' => $this->faker->randomFloat(2, 10, 500),  // Valor total entre 10 e 500
            'order_data' => json_encode($orderData),  // Converte o array para JSON
        ];
    }

    // Função para criar pedidos com produtos associados
    public function withProducts($count = 1)
    {
        $products = Product::inRandomOrder()->limit($count)->get();

        $orderData = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'quantity' => $this->faker->numberBetween(1, 5),
                'description' => $product->description,
            ];
        });

        return $this->state([
            'order_data' => json_encode($orderData),  // Adiciona os produtos ao pedido
        ]);
    }
}
