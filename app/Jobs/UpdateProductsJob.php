<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateProductsJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    protected $productData;
    protected $productIds;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($productIds, $productData)
    {
        $this->productIds = $productIds;
        $this->productData = $productData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->productIds as $index => $productId) {
            $product = Product::find($productId);

            if ($product) {
                $data = $this->productData->get($index)->toArray(); 

                $product->update($data);
            }
        }

        Cache::forget('products_list');
    }
}

