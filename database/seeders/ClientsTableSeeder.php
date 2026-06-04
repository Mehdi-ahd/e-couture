<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientsTableSeeder extends Seeder
{
    public function run(): void
    {
        // For each prestataire (role couturier) create two clients
        $prestataires = User::query()->role(User::ROLE_COUTURIER)->get();

        $beninClients = [
            ['nom' => 'Kodjo', 'prenom' => 'Brice'],
            ['nom' => 'Kouton', 'prenom' => 'Marie'],
            ['nom' => 'Agan', 'prenom' => 'Rachelle'],
            ['nom' => 'Bankole', 'prenom' => 'Pascal'],
            ['nom' => 'Dossou', 'prenom' => 'Aicha'],
            ['nom' => 'Tognon', 'prenom' => 'Henri'],
            ['nom' => 'Soglo', 'prenom' => 'Monique'],
            ['nom' => 'Kouassi', 'prenom' => 'David'],
        ];

        $i = 0;
        foreach ($prestataires as $prestataire) {
            for ($n = 0; $n < 2; $n++) {
                $entry = $beninClients[$i % count($beninClients)];
                Client::query()->updateOrCreate(
                    [
                        'telephone' => sprintf('+22990%06d', 100 + $i),
                    ],
                    [
                        'nom' => $entry['nom'],
                        'prenom' => $entry['prenom'],
                        'email' => strtolower($entry['prenom']).'.'.strtolower($entry['nom']).'@example.bj',
                        'prestataire_id' => $prestataire->id,
                        'genre' => $i % 2 === 0 ? 'feminin' : 'masculin',
                        'est_actif' => true,
                    ],
                );

                $i++;
            }
        }
    }
}
