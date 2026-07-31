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

        $defaultCodes = ['POITRINE', 'CEINTURE', 'TOUR_FESSES', 'EPAULES', 'HAUTEUR'];

        foreach ($clients as $client) {
            if ($client->nom === 'PATINDE' && $client->prenom === 'Aarone') {
                continue;
            }
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

        // --- Mesures réelles pour PATINDE Aarone ---
        $aarone = Client::where('nom', 'PATINDE')->where('prenom', 'Aarone')->first();
        if ($aarone && $typeMesures->isNotEmpty()) {
            $fiche = FicheMesure::query()->firstOrCreate(
                ['client_id' => $aarone->id, 'date' => now()->toDateString()],
                ['methode' => 'automatique'],
            );

            $mesures = [
                'BRA_AV'            => 27.7,
                'CARRURE_DEVANT'    => 35.7,
                'CARRURE_DOS'       => 35.6,
                'HAUT_SEIN'         => 16.0,
                'HAUTEUR_GENOU'     => 54.2,
                'HAUTEUR'           => 150.0,
                'HANCHES_L'         => 22.4,
                'EPAULES'           => 37.8,
                'LONGUEUR_CHEMISE'  => 21.0,
                'CUISSE'            => 40.1,
                'JAMBE'             => 79.3,
                'LONGUEUR_JUPE'     => 79.3,
                'MANCHE_COURTE'     => 32.3,
                'MANCHE_LONGUE'     => 59.6,
                'MOLLET'            => 45.0,
                'LONGUEUR_ROBE'     => 132.4,
                'LONGUEUR_SOUS_SEINS' => 11.0,
                'LONGUEUR_TAILLE'   => 34.2,
                'TORSE'             => 51.8,
                'CEINTURE'          => 78.4,
                'TOUR_COU'          => 22.8,
                'TOUR_FESSES'       => 76.1,
                'TOUR_GENOU'        => 54.0,
                'TOUR_POIGNET'      => 18.0,
                'POITRINE'          => 89.5,
                'TOUR_SOUS_SEINS'   => 92.9,
                'TOUR_BAS'          => 29.2,
            ];

            foreach ($mesures as $code => $valeur) {
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
                        'valeur' => $valeur,
                        'source' => 'manuel',
                        'confiance' => 1.0,
                    ],
                );
            }
        }
    }
}
