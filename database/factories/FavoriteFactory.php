<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Favorite>
 */
class FavoriteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(), // Asegúrate de tener un usuario relacionado
            'name' => $this->faker->city, // Genera un nombre de ciudad aleatorio
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}