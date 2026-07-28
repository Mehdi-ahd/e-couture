<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\CommandeVetement;
use App\Models\FicheMesure;
use App\Models\ModeleVetement;
use Illuminate\Database\Seeder;

class CommandeVetementsTableSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all();
        $modeles = ModeleVetement::query()->orderBy('nom')->get();

        if ($modeles->isEmpty()) {
            return;
        }

        $statuses = ['en_cours', 'valide', 'livree', 'brouillon'];

        foreach ($clients as $index => $client) {
            $fiche = FicheMesure::query()->where('client_id', $client->id)->first();
            $modele = $modeles[$index % $modeles->count()];

            CommandeVetement::query()->updateOrCreate(
                [
                    'client_id' => $client->id,
                    'modele_vetement_id' => $modele->id,
                    'date_commande' => now()->subDays(rand(1, 60))->toDateString(),
                ],
                [
                    'fiche_mesure_id' => $fiche?->id,
                    'statut' => $statuses[$index % count($statuses)],
                    'date_livraison' => now()->addDays(rand(3, 21))->toDateString(),
                    'notes' => 'Commande d\'exemple générée pour les tests atelier KODA.',
                ],
            );
        }
    }
}
