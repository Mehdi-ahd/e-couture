<?php

namespace Database\Seeders;

use App\Models\FormeDecoupe;
use Illuminate\Database\Seeder;

class FormesDecoupeTableSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'nom' => 'Coupe droite',
                'description' => 'Découpe linéaire simple adaptée aux jupes, pantalons et panneaux droits.',
                'donnees_formes' => ['kind' => 'line', 'points' => [[0, 0], [1, 0]]],
            ],
            [
                'nom' => 'Coupe cintrée',
                'description' => 'Découpe courbe pour silhouettes ajustées au niveau du buste ou de la taille.',
                'donnees_formes' => ['kind' => 'curve', 'points' => [[0, 0], [0.4, 0.2], [1, 0.8]]],
            ],
            [
                'nom' => 'Encolure ronde',
                'description' => 'Base d\'encolure ronde, fréquente sur robes, tuniques et hauts simples.',
                'donnees_formes' => ['kind' => 'arc', 'radius' => 0.5],
            ],
            [
                'nom' => 'Encolure V',
                'description' => 'Découpe d\'encolure en V pour hauts féminins et boubous modernes.',
                'donnees_formes' => ['kind' => 'polyline', 'points' => [[0, 0], [0.5, 1], [1, 0]]],
            ],
            [
                'nom' => 'Manche montée',
                'description' => 'Découpe de manche structurée pour chemises, vestes et robes ajustées.',
                'donnees_formes' => ['kind' => 'sleeve', 'capHeight' => 0.35],
            ],
            [
                'nom' => 'Fente latérale',
                'description' => 'Ouverture latérale utilisée sur kaba, jupes ou tuniques longues.',
                'donnees_formes' => ['kind' => 'slit', 'depth' => 0.45],
            ],
            [
                'nom' => 'Pince poitrine',
                'description' => 'Pince pour ajuster le tombé au niveau du buste.',
                'donnees_formes' => ['kind' => 'dart', 'length' => 0.25],
            ],
            [
                'nom' => 'Godet',
                'description' => 'Ajout d\'ampleur sur jupes et robes par insertion triangulaire.',
                'donnees_formes' => ['kind' => 'triangle', 'width' => 0.4, 'height' => 0.8],
            ],
        ];

        foreach ($items as $item) {
            FormeDecoupe::query()->updateOrCreate(
                ['nom' => $item['nom']],
                [
                    'description' => $item['description'],
                    'donnees_formes' => $item['donnees_formes'],
                    'source' => 'seed_benin_2026',
                    'est_global' => true,
                ],
            );
        }
    }
}
