<?php

namespace Database\Seeders;

use App\Models\CommandeVetement;
use App\Models\FicheMesure;
use App\Models\Client;
use Illuminate\Database\Seeder;

class CommandeVetementsTableSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all();

        foreach ($clients as $client) {
            $fiche = FicheMesure::query()->where('client_id', $client->id)->first();

            CommandeVetement::query()->create([
                'client_id' => $client->id,
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
