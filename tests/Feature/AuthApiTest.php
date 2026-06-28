<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'login' => 'joao',
            'codigo' => '123-456',
            'senha' => 'senha123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'login', 'codigo', 'created_at', 'updated_at'],
                'token',
                'token_type',
            ]);

        $this->assertDatabaseHas('users', [
            'login' => 'joao',
            'codigo' => '123-456',
        ]);
    }

    public function test_user_can_login_with_login_and_password(): void
    {
        User::create([
            'login' => 'maria',
            'codigo' => '999-888',
            'senha' => 'senha123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'login' => 'maria',
            'senha' => 'senha123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'login', 'codigo', 'created_at', 'updated_at'],
                'token',
                'token_type',
            ]);
    }

    public function test_user_can_login_with_codigo_and_password(): void
    {
        User::create([
            'login' => 'ana',
            'codigo' => '321-654',
            'senha' => 'senha123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'codigo' => '321-654',
            'senha' => 'senha123',
        ]);

        $response->assertOk();
    }
}