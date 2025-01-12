<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Jobs\SendOrderStatusUpdateEmail;
use Illuminate\Support\Facades\Bus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class OrderTest extends TestCase
{

    protected $token;

    /** 
     * Configuração inicial para o teste.
     * Gerando o token JWT para autenticação em todas as requisições.
     */
    public function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create([
            'roles' => 'admin',
        ]);

        $this->token = JWTAuth::fromUser($user);
    }

    public function test_index_caches_orders_if_not_cached()
    {
        $orders = Order::factory()->count(3)->create();

        $response = $this->getJson('/api/orders', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'user_id', 'status', 'total_value']
        ]);
    }

    public function test_store_creates_order_and_dispatches_job()
    {

        $user = User::factory()->create([
            'roles' => 'admin',
        ]);

        $product = Product::factory()->create();

        $data = [
            'user_id' => $user->id,
            'products' => [
                [
                    'id' => $product->id,
                    'quantity' => 2
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $data, [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Pedido recebido e sendo processado.'
        ]);
    }

    public function test_it_updates_the_order_status_and_dispatches_email_job()
    {
        $user = User::factory()->create([
            'roles' => 'admin',
        ]);
        $order = Order::factory()->create(['user_id' => $user->id]);

        Bus::fake();

        $status = 'processing';

        $response = $this->patchJson('/api/orders/'.$order->id.'/status', [
            'status' => $status,
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $order->refresh();
        $this->assertEquals($status, $order->status);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'message' => 'Status atualizado e e-mail enviado com sucesso.',
            'order' => [
                'id' => $order->id,
                'status' => $status,
            ],
        ]);

        Bus::assertDispatched(SendOrderStatusUpdateEmail::class);
    }
}