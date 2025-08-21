<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

return [
    'type' => fake()->randomElement(['residentiel','professionnel']),
    'nom' => fake()->lastName(),
    'prenom' => fake()->firstName(),
    'raison_sociale' => fake()->company(),
    'telephone' => fake()->phoneNumber(),
    'email' => fake()->unique()->safeEmail(),
    'adresse_ligne1' => fake()->streetAddress(),
    'adresse_ligne2' => fake()->secondaryAddress(),
    'ville' => fake()->city(),
    'zone' => fake()->word(),
    'latitude' => fake()->latitude(),
    'longitude' => fake()->longitude(),
];
    }
}
