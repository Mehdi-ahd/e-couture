<?php

namespace Database\Seeders;

use App\Models\CommandeVetement;
use App\Models\Client;
use App\Models\FicheMesure;
use Illuminate\Database\Seeder;

class CommandeVetementsTableSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all();

        foreach ($clients as $client) {
            $fiche = FicheMesure::query()->where('fiche_client_id', $client->id)->first();

            CommandeVetement::query()->create([
                'fiche_client_id' => $client->id,
                'modele_vetement_id' => 1,
                'fiche_mesure_id' => $fiche?->id,
                'statut' => 'fini',
                'date_commande' => now()->subDays(rand(1, 200)),
                'date_livraison' => now()->addDays(rand(1, 30)),
                'notes' => 'Commande initiale générée par le seeder',
            ]);
        }
    }
}
