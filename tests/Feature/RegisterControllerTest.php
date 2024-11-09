<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    //use RefreshDatabase;

    /** @test */
    public function it_registers_a_user_with_valid_data()
    {
        // Datos de prueba
        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
        ];

        // Realiza la solicitud de registro
        $response = $this->postJson('api/users/register', $data);

        // Verifica el estado de respuesta y la estructura JSON
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'user' => [
                         'id',
                         'name',
                         'email',
                         'created_at',
                         'updated_at'
                     ]
                 ]);

        // Verifica que el usuario se haya creado en la base de datos
        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@example.com',
        ]);

        // Verifica que la contraseña se haya almacenado encriptada
        $user = User::where('email', 'johndoe@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /** @test */
    public function it_requires_name_email_and_password()
    {
        // Realiza la solicitud de registro con datos incompletos
        $response = $this->postJson('api/users/register', []);

      

        // Verifica que falten los campos requeridos
        $response->assertStatus(422);
        $response->assertJsonFragment([
            'name' => ['The name field is required.'],
            'email' => ['The email field is required.'],
            'password' => ['The password field is required.'],
        ]);
    }


    /** @test */
    public function it_requires_unique_email()
    {
        // Crea un usuario en la base de datos
        User::factory()->create([
            'email' => 'johndoe@example.com',
        ]);

        // Datos de prueba con el mismo email
        $data = [
            'name' => 'Jane Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        
        // Realiza la solicitud de registro en la ruta /api/register
        $response = $this->postJson('/api/users/register', $data);

        // Verifica que se rechace por email duplicado
        $response->assertStatus(422);
        $response->assertJsonFragment([
            'email' => ['The email has already been taken.'],
        ]);
    }
}

