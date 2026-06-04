<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\FicheMesure;
use App\Models\LigneMensuration;
use App\Models\TypeMesure;
use Illuminate\Database\Seeder;

class FichesMesuresTableSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all();
        $typeIds = TypeMesure::query()
            ->whereIn('code', ['poitrine', 'taille', 'hanche', 'epaule', 'longueur_robe'])
            ->pluck('id', 'code');

        foreach ($clients as $client) {
            $date = now()->subDays(rand(0, 120))->toDateString();
            $fiche = FicheMesure::query()->firstOrCreate([
                'client_id' => $client->id,
                'date' => $date,
            ], [
                'methode' => 'manuelle',
            ]);

            $types = array_values($typeIds->all());
            foreach ($types as $idx => $typeId) {
                LigneMensuration::query()->updateOrCreate([
                    'fiche_mesure_id' => $fiche->id,
                    'type_mesure_id' => $typeId,
                ], [
                    'valeur' => 78 + $idx * 4 + rand(0, 6),
                    'source' => 'manuel',
                    'confiance' => 0.92,
                ]);
            }
        }
    }
}
