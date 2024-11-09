<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Favorite;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FavoritesControllerTest extends TestCase
{
    use RefreshDatabase;

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


    /**
     * Test para verificar que un usuario autenticado puede listar sus ciudades favoritas.
     */
    public function test_usuario_autenticado_puede_listar_favoritos()
    {
        // Crear un usuario
        $user = User::factory()->create();

        // Crear ciudades favoritas para el usuario
        Favorite::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        // Hacer la solicitud autenticada como el usuario
        $response = $this->actingAs($user, 'api')
                         ->getJson('/api/ciudades/favoritas');

        // Verificar la respuesta
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'favorites' => [
                         '*' => [
                             'id',
                             'user_id',
                             'name',
                             'created_at',
                             'updated_at'
                         ]
                     ]
                 ]);

        // Verificar que la cantidad de ciudades favoritas es correcta
        $this->assertCount(3, $response->json('favorites'));
    }

    /**
     * Test para verificar que un usuario no autenticado no puede listar ciudades favoritas.
     */
    public function test_usuario_no_autenticado_no_puede_listar_favoritos()
    {
        // Hacer la solicitud sin autenticación
        $response = $this->getJson('/api/ciudades/favoritas');

        // Verificar que la respuesta sea de no autenticado
        $response->assertStatus(401);
    }

    /**
     * Test para verificar que un usuario autenticado puede eliminar un favorito.
     */
    public function test_usuario_autenticado_puede_eliminar_favorito()
    {
        // Crear un usuario y un favorito
        $user = User::factory()->create();
        $favorite = Favorite::factory()->create(['user_id' => $user->id]);

        // Hacer la solicitud autenticada para eliminar el favorito
        $response = $this->actingAs($user, 'api')
                         ->deleteJson("/api/ciudades/favoritas/{$favorite->id}");

        // Verificar la respuesta
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Favorite deleted successfully']);

        // Verificar que el registro haya sido eliminado de la base de datos
        $this->assertDatabaseMissing('favorites', ['id' => $favorite->id]);
    }

    /**
     * Test para verificar que un usuario no puede eliminar un favorito que no le pertenece.
     */
    public function test_usuario_no_puede_eliminar_favorito_que_no_le_pertenece()
    {
        // Crear dos usuarios y un favorito para uno de ellos
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $favorite = Favorite::factory()->create(['user_id' => $user1->id]);

        // Hacer la solicitud autenticada como el segundo usuario
        $response = $this->actingAs($user2, 'api')
                         ->deleteJson("/api/ciudades/favoritas/{$favorite->id}");

        // Verificar la respuesta
        $response->assertStatus(404)
                 ->assertJson(['error' => 'Favorite not found or does not belong to the user']);

        // Verificar que el registro aún existe en la base de datos
        $this->assertDatabaseHas('favorites', ['id' => $favorite->id]);
    }

    /**
     * Test para verificar que un usuario no autenticado no puede eliminar un favorito.
     */
    public function test_usuario_no_autenticado_no_puede_eliminar_favorito()
    {
        // Crear un favorito
        $favorite = Favorite::factory()->create();

        // Hacer la solicitud sin autenticación
        $response = $this->deleteJson("/api/ciudades/favoritas/{$favorite->id}");

        // Verificar la respuesta de no autenticado
        $response->assertStatus(401);

        // Verificar que el registro aún existe en la base de datos
        $this->assertDatabaseHas('favorites', ['id' => $favorite->id]);
    }
}

