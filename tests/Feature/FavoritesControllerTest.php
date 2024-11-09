<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Favorite;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FavoritesControllerTest extends TestCase
{
    //use RefreshDatabase;

    /**
     * Test para verificar que un usuario autenticado puede agregar una ciudad a favoritos.
     */
    public function test_usuario_autenticado_puede_agregar_favorito()
    {
        // Crear un usuario
        $user = User::factory()->create();

        // Datos de prueba para la solicitud
        $data = [
            'name' => 'Ciudad de Prueba'
        ];

        // Hacer la solicitud autenticada como el usuario
        $response = $this->actingAs($user, 'api')
                         ->postJson('/api/ciudades/favoritas', $data);

        // Verificar la respuesta
        $response->assertStatus(201)
                 ->assertJson([
                     'message' => 'Ciudad añadida a favoritos exitosamente',
                     'ciudad' => [
                         'name' => 'Ciudad de Prueba'
                     ]
                 ]);

        // Verificar que el registro se encuentra en la base de datos
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'name' => 'Ciudad de Prueba'
        ]);
    }

    /**
     * Test para verificar que la validación de datos funciona.
     */
    public function test_no_se_puede_agregar_favorito_sin_nombre()
    {
        // Crear un usuario
        $user = User::factory()->create();

        // Datos de prueba vacíos
        $data = [
            'name' => ''
        ];

        // Hacer la solicitud autenticada como el usuario
        $response = $this->actingAs($user, 'api')
                         ->postJson('/api/ciudades/favoritas', $data);

        // Verificar la respuesta de validación
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test para verificar que un usuario no autenticado no pueda agregar una ciudad a favoritos.
     */
    public function test_usuario_no_autenticado_no_puede_agregar_favorito()
    {
        // Datos de prueba para la solicitud
        $data = [
            'name' => 'Ciudad de Prueba'
        ];

        // Hacer la solicitud sin autenticación
        $response = $this->postJson('/api/ciudades/favoritas', $data);

        // Verificar que la respuesta sea de no autenticado
        $response->assertStatus(401);
    }
}

