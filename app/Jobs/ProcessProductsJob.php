<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ProcessProductsJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    protected $products;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($products)
    {
        $this->products = $products;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->products as $productData) {
            Log::error($productData);
            Product::create([
                'name' => $productData['product_name'],
                'unit_price' => $productData['unit_value'],
                'description' => $productData['product_description'],
                'available_quantity' => $productData['avaliable_quantity'],
                'SKU' => $productData['SKU']
            ]);
        }
    }
}
