<?php

namespace Database\Factories;

use App\Models\TypeVetement;
use Illuminate\Database\Eloquent\Factories\Factory;

class TypeVetementFactory extends Factory
{
    protected $model = TypeVetement::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->lexify('????'),
            'nom' => fake()->word(),
            'description' => fake()->sentence(),
            'genre' => fake()->randomElement(['homme', 'femme', 'mixte']),
            'section' => fake()->randomElement(['adulte', 'enfant']),
            'est_actif' => true,
        ];
    }
}
