<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'prestataire_id' => User::factory(),
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'telephone' => fake()->unique()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'genre' => fake()->randomElement(['homme', 'femme']),
            'est_actif' => true,
        ];
    }
}
