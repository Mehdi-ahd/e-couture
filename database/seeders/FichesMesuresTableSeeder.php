<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\FicheMesure;
use App\Models\LigneMensuration;
use Illuminate\Database\Seeder;

class FichesMesuresTableSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all();

        foreach ($clients as $client) {
            $fiche = FicheMesure::query()->create([
                'fiche_client_id' => $client->id,
                'date' => now()->subDays(rand(0, 365)),
                'methode' => 'manuelle',
            ]);

            // Create a few measurement lines
            $types = [1,2,3,4,5];
            foreach ($types as $idx => $typeId) {
                LigneMensuration::query()->create([
                    'fiche_mesure_id' => $fiche->id,
                    'type_mesure_id' => $typeId,
                    'valeur' => 50 + $idx * 2 + rand(0, 5),
                    'source' => 'manuel',
                    'confiance' => 0.9,
                ]);
            }
        }
    }
}
