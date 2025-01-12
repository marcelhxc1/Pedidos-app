<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductTest extends TestCase
{

    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->token = JWTAuth::fromUser($user);
    }

    public function test_index_returns_products_from_cache()
    {
        $products = Product::factory()->count(5)->make();
        Cache::put('products_list', $products, now()->addMinutes(10));

        $response = $this->getJson('/api/products', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJson($products->toArray());
    }

    public function test_show_returns_product_if_found()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}", [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJson($product->toArray());
    }

    public function test_show_returns_404_if_product_not_found()
    {
        $response = $this->getJson('/api/products/999', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Product not found']);
    }
}