<?php

namespace Database\Seeders;

use App\Models\CommandeVetement;
use App\Models\Client;
use App\Models\FicheMesure;
use App\Models\ModeleVetement;
use Illuminate\Database\Seeder;

class CommandeVetementsTableSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all();
        $models = ModeleVetement::query()->orderBy('nom')->get();

        if ($models->isEmpty()) {
            return;
        }

        foreach ($clients as $index => $client) {
            $fiche = FicheMesure::query()->where('client_id', $client->id)->first();
            $model = $models[$index % $models->count()];

            CommandeVetement::query()->updateOrCreate([
                'client_id' => $client->id,
                'modele_vetement_id' => $model->id,
                'date_commande' => now()->subDays(rand(1, 60))->toDateString(),
            ], [
                'fiche_mesure_id' => $fiche?->id,
                'statut' => ['en_cours', 'valide', 'livree'][$index % 3],
                'date_livraison' => now()->addDays(rand(3, 21))->toDateString(),
                'notes' => 'Commande d\'exemple générée pour les tests atelier KODA.',
            ]);
        }
    }
}
