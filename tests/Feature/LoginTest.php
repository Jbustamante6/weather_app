<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_logs_in_a_user_with_valid_credentials()
    {
        // Crea un usuario en la base de datos
        $user = User::factory()->create([
            'email' => 'johndoe@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Datos de login
        $data = [
            'email' => 'johndoe@example.com',
            'password' => 'password123',
        ];

        // Realiza la solicitud de login
        $response = $this->postJson('/api/users/login', $data);

        // Verifica que el estado de respuesta sea 200 y que el token esté presente
        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);
    }

    /** @test */
    public function it_fails_to_log_in_with_invalid_credentials()
    {
        // Crea un usuario en la base de datos
        $user = User::factory()->create([
            'email' => 'johndoe@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Datos de login incorrectos
        $data = [
            'email' => 'johndoe@example.com',
            'password' => 'wrongpassword',
        ];

        // Realiza la solicitud de login
        $response = $this->postJson('/api/users/login', $data);

        // Verifica que el estado de respuesta sea 401
        $response->assertStatus(401);
        $response->assertJson(['error' => 'Invalid credentials']);
    }
}

