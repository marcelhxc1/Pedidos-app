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

class UsersTest extends TestCase
{

    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create([
            'roles' => 'admin',
        ]);
        $this->token = JWTAuth::fromUser($user);
    }

    public function test_store_creates_user_and_dispatches_job()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
    
        $response = $this->postJson('/api/users', $data,  [
            'Authorization' => 'Bearer ' . $this->token,
        ]);
    
        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Usuário sendo criado...',
        ]);
    }

    public function test_index_returns_all_users()
    {
        User::factory()->count(3)->create();
    
        $response = $this->getJson('/api/users', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);
    
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'name', 'email', 'created_at', 'updated_at'],
        ]);
    }

    public function test_show_returns_user_details()
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}", [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    public function test_show_returns_404_if_user_not_found()
    {
        $response = $this->getJson('/api/users/9999', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Usuário não encontrado.',
        ]);
    }

    public function test_update_updates_user_and_dispatches_job()
    {
        $user = User::factory()->create([
            'roles' => 'admin',
        ]);

        $data = [
            'name' => 'Updated Name',
            'email' => 'updatedemail@example.com',
            'password' => 'newpassword123',
        ];
    
        $response = $this->putJson("/api/users/{$user->id}", $data, [
            'Authorization' => 'Bearer ' . $this->token,
        ]);
    
        $response->assertStatus(202);
        $response->assertJson([
            'message' => 'Atualização em andamento.',
        ]);
    
        $user->refresh();

    }
}        