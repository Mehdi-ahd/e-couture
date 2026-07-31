<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientsTableSeeder extends Seeder
{
    public function run(): void
    {
        $prestataires = User::query()->role(User::ROLE_COUTURIER)->get();

        $beninClients = [
            ['nom' => 'Kodjo',    'prenom' => 'Brice',   'genre' => 'homme'],
            ['nom' => 'Kouton',   'prenom' => 'Marie',   'genre' => 'femme'],
            ['nom' => 'Agan',     'prenom' => 'Rachelle', 'genre' => 'femme'],
            ['nom' => 'Bankole',  'prenom' => 'Pascal',  'genre' => 'homme'],
            ['nom' => 'Dossou',   'prenom' => 'Aicha',   'genre' => 'femme'],
            ['nom' => 'Tognon',   'prenom' => 'Henri',   'genre' => 'homme'],
            ['nom' => 'Soglo',    'prenom' => 'Monique', 'genre' => 'femme'],
            ['nom' => 'Kouassi',  'prenom' => 'David',   'genre' => 'homme'],
            ['nom' => 'Hounsou',  'prenom' => 'Gisèle',  'genre' => 'femme'],
            ['nom' => 'Assogba',  'prenom' => 'Marc',    'genre' => 'homme'],
        ];

        $i = 0;
        foreach ($prestataires as $prestataire) {
            for ($n = 0; $n < 2; $n++) {
                $entry = $beninClients[$i % count($beninClients)];
                Client::query()->updateOrCreate(
                    ['telephone' => sprintf('+22990%06d', 100 + $i)],
                    [
                        'nom' => $entry['nom'],
                        'prenom' => $entry['prenom'],
                        'email' => strtolower($entry['prenom']).'.'.strtolower($entry['nom']).'@example.bj',
                        'genre' => $entry['genre'],
                        'prestataire_id' => $prestataire->id,
                        'est_actif' => true,
                    ],
                );
                $i++;
            }
        }

        // Lier PATINDE Aarone à la couturière nolanpatinde06@gmail.com
        $prestataire = User::where('email', 'nolanpatinde06@gmail.com')->first();
        if ($prestataire) {
            Client::query()->updateOrCreate(
                ['telephone' => '+22990123456'],
                [
                    'nom' => 'PATINDE',
                    'prenom' => 'Aarone',
                    'email' => 'aarone.patinde@example.bj',
                    'genre' => 'femme',
                    'prestataire_id' => $prestataire->id,
                    'est_actif' => true,
                ],
            );
        }
    }
}
