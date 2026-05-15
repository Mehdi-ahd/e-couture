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
                Client::query()->create([
                    'nom' => $entry['nom'],
                    'prenom' => $entry['prenom'],
                    'telephone' => sprintf('+22990%06d', 100 + $i),
                    'email' => strtolower($entry['prenom']).'.'.strtolower($entry['nom']).'@example.bj',
                    'genre' => $n % 2 == 0 ? 'femme' : 'homme',
                    'est_actif' => true,
                    'prestataire_id' => $prestataire->id,
                ]);

                $i++;
            }
        }
    }
}
