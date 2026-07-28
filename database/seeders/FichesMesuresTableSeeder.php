<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\FicheMesure;
use App\Models\Mesure;
use App\Models\TypeMesure;
use Illuminate\Database\Seeder;

class FichesMesuresTableSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all();
        $typeMesures = TypeMesure::query()->pluck('id', 'code');

        $defaultCodes = ['POITRINE', 'TAILLE', 'TOUR_HANCHES', 'EPAULES', 'HAUTEUR'];

        foreach ($clients as $client) {
            $date = now()->subDays(rand(0, 120))->toDateString();
            $fiche = FicheMesure::query()->firstOrCreate(
                ['client_id' => $client->id, 'date' => $date],
                ['methode' => 'manuelle'],
            );

            foreach ($defaultCodes as $code) {
                $typeId = $typeMesures[$code] ?? null;
                if ($typeId === null) {
                    continue;
                }

                Mesure::query()->updateOrCreate(
                    [
                        'fiche_mesure_id' => $fiche->id,
                        'type_mesure_id' => $typeId,
                    ],
                    [
                        'valeur' => rand(60, 120) + rand(0, 99) / 100,
                        'source' => 'manuel',
                        'confiance' => 0.92,
                    ],
                );
            }
        }
    }
}
