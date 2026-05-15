<?php

namespace Database\Seeders;

use App\Models\ModeleVetement;
use App\Models\TypeVetement;
use App\Models\User;
use Illuminate\Database\Seeder;

class ModeleVetementsTableSeeder extends Seeder
{
    public function run(): void
    {
        $type = TypeVetement::query()->first();
        $prest = User::query()->role(User::ROLE_COUTURIER)->first();

        if ($type === null) {
            return;
        }

        ModeleVetement::query()->create([
            'nom' => 'Modèle de base',
            'description' => 'Modèle minimal créé par le seeder',
            'portee' => 'public',
            'statut' => 'publie',
            'prestataire_id' => $prest?->id,
            'type_vetement_id' => $type->id,
        ]);
    }
}
