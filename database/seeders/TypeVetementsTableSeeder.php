<?php

namespace Database\Seeders;

use App\Models\TypeVetement;
use Illuminate\Database\Seeder;

class TypeVetementsTableSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'haut', 'nom' => 'Haut', 'description' => 'Haut standard', 'est_actif' => true],
            ['code' => 'bas', 'nom' => 'Bas', 'description' => 'Bas standard', 'est_actif' => true],
        ];

        foreach ($types as $t) {
            TypeVetement::query()->create($t);
        }
    }
}
