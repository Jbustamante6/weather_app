<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Support\Facades\Http;


class WeatherServiceTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function it_requires_query_parameter_with_min_length()
    {
        $response = $this->getJson('/api/search-cities?q=ab'); // Menos de 3 caracteres

        $response->assertStatus(422); // Código de error de validación
        $response->assertJson(['error' => 'El campo "q" es obligatorio y debe tener al menos 3 caracteres.']);
    }

    /** @test */
    public function it_returns_weather_data_for_valid_city_query()
    {
        // Simula la respuesta de la API con Http::fake()
        Http::fake([
            'pro.openweathermap.org/*' => Http::response([
                "cod" => "200",
                "city" => [
                    "name" => "Madrid",
                    "country" => "ES",
                ],
                "list" => [
                    [
                        "main" => [
                            "temp" => 286.72,
                        ],
                        "weather" => [
                            [
                                "description" => "cielo claro",
                            ]
                        ],
                    ]
                ]
            ], 200)
        ]);

        // Realiza la solicitud al endpoint real de tu aplicación
        $response = $this->getJson('/api/search-cities?q=Madrid');

        // Verifica el código de estado y la estructura JSON
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'cod',
            'city' => [
                'name',
                'country',
            ],
            'list' => [
                '*' => [
                    'main' => [
                        'temp',
                    ],
                    'weather' => [
                        '*' => [
                            'description',
                        ]
                    ],
                ]
            ]
        ]);
    }

    /** @test */
    public function it_throws_an_exception_when_api_request_fails()
    {
        $query = 'InvalidCity';

        $response = $this->getJson('/api/search-cities?q=' . $query);

        $response->assertStatus(500); // Error interno del servidor
        $response->assertJson(['error' => 'API Error']);
    }
}
