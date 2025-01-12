<?php

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\MfaMail;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class AuthControllerTest extends TestCase
{
    public function test_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test4@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test4@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Código MFA enviado para seu e-mail. Por favor, verifique para concluir o login.']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'mfa' => $user->fresh()->mfa, 
        ]);

        $mfaExpiresAt = Carbon::parse($user->fresh()->mfa_expires_at);

        $this->assertTrue(
            $mfaExpiresAt->isAfter(now()) &&
            $mfaExpiresAt->isBefore(now()->addMinutes(11))
        );
    }
    public function test_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Unauthorized']);
    }

    public function test_login_validation_error()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(400);
        $response->assertJsonStructure(['errors']);
    }

    public function test_verify_mfa_success()
    {
        $user = User::factory()->create([
            'email' => 'testmfaverify@example.com',
            'mfa' => '123456',
            'mfa_expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/verify-mfa', [
            'email' => 'testmfaverify@example.com',
            'mfa' => '123456',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Autenticação MFA bem-sucedida.']);
        $response->assertJsonStructure(['token']);

        $token = $response->json('token');
        $this->assertNotEmpty($token);
        JWTAuth::setToken($token);
        $this->assertTrue(JWTAuth::check());
    }

    public function test_verify_mfa_invalid_code()
    {
        $user = User::factory()->create([
            'email' => 'testmfaverifyinvalid@example.com',
            'mfa' => '123456',
            'mfa_expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/verify-mfa', [
            'email' => 'testmfaverifyinvalid@example.com',
            'mfa' => '654321',
        ]);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Código MFA inválido ou expirado.']);
    }

    public function test_verify_mfa_expired_code()
    {
        $user = User::factory()->create([
            'email' => 'testmfaverifyexpired@example.com',
            'mfa' => '123456',
            'mfa_expires_at' => now()->subMinutes(10),
        ]);

        $response = $this->postJson('/api/verify-mfa', [
            'email' => 'testmfaverifyexpired@example.com',
            'mfa' => '123456',
        ]);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Código MFA inválido ou expirado.']);
    }
}


