<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Models\Product;
use App\Mail\OrderCreated;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CreateOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $products;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $products)
    {
        $this->user = $user;
        $this->products = $products;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $totalValue = $this->calculateTotalValue($this->products);

        if ($totalValue <= 0) {
            throw new \Exception("O valor total do pedido não pode ser zero ou negativo.");
        }

        $order = Order::create([
            'user_id' => $this->user->id,
            'total_value' => $totalValue,
            'status' => 'pending',
            'order_data' => json_encode($this->products),
        ]);

        foreach ($this->products as $product) {
            $productDetails = Product::find($product['id']);
    
            $subtotal = $productDetails->unit_price * $product['quantity'];

            if ($productDetails->available_quantity < $product['quantity']) {
                throw new \Exception("Estoque insuficiente para o produto {$productDetails->name}.");
            }
    
            $productDetails->available_quantity -= $product['quantity'];
            $productDetails->save();
    
            $order->products()->attach($product['id'], [
                'quantity' => $product['quantity'],
                'subtotal' => $subtotal,
            ]);
        }

        Mail::to($this->user->email)->send(new OrderCreated($order));
    }

    private function calculateTotalValue($products)
    {
        $totalValue = 0;

        foreach ($products as $product) {
            $productDetails = Product::find($product['id']);
            
            if ($productDetails) {
                $price = (float) $productDetails->unit_price;
                
                $quantity = (int) $product['quantity'];

                Log::info("Produto encontrado: " . $productDetails->name . " - Preço: " . $price . " - Quantidade: " . $quantity);
                
                $totalValue += $price * $quantity;
            } else {
                Log::error("Produto com ID {$product['id']} não encontrado.");
                throw new \Exception("Produto com ID {$product['id']} não encontrado.");
            }
        }

        if ($totalValue <= 0) {
            Log::error("O valor total do pedido é inválido.");
            throw new \Exception("O valor total do pedido é inválido.");
        }

        return $totalValue;
    }
}
