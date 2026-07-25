<?php

namespace Database\Factories;

use App\Models\ModeleVetement;
use App\Models\TypeVetement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModeleVetementFactory extends Factory
{
    protected $model = ModeleVetement::class;

    public function definition(): array
    {
        return [
            'prestataire_id' => User::factory(),
            'type_vetement_id' => TypeVetement::factory(),
            'nom' => fake()->word(),
            'description' => fake()->sentence(),
            'portee' => 'prive',
            'statut' => 'actif',
        ];
    }
}
