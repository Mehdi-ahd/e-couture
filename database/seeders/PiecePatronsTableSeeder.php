<?php

namespace Database\Seeders;

use App\Models\Patron;
use App\Models\PiecePatron;
use Illuminate\Database\Seeder;

class PiecePatronsTableSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            'Robe kaba ajustée' => ['Devant robe', 'Dos robe', 'Manche gauche', 'Manche droite', 'Parementure col'],
            'Ensemble wax Cotonou' => ['Devant haut', 'Dos haut', 'Manche', 'Devant pantalon', 'Dos pantalon', 'Ceinture'],
            'Boubou homme Porto-Novo' => ['Devant boubou', 'Dos boubou', 'Manche longue', 'Col'],
            'Chemise africaine col mao' => ['Devant chemise', 'Dos chemise', 'Manche gauche', 'Manche droite', 'Col mao', 'Poignet'],
            'Jupe taille haute wax' => ['Devant jupe', 'Dos jupe', 'Ceinture'],
            'Tenue sortie de mairie' => ['Devant robe', 'Dos robe', 'Manche', 'Ceinture'],
        ];

        $patrons = Patron::query()->with('modeleVetement')->get();

        foreach ($patrons as $patron) {
            $modelName = $patron->modeleVetement?->nom;
            $pieces = $definitions[$modelName] ?? null;

            if ($pieces === null) {
                continue;
            }

            foreach ($pieces as $index => $name) {
                PiecePatron::query()->updateOrCreate(
                    [
                        'patron_id' => $patron->id,
                        'nom' => $name,
                    ],
                    [
                        'ordre' => $index + 1,
                        'donnees_geometriques' => [
                            'seed' => 'benin_2026',
                            'anchors' => [
                                ['x' => 0, 'y' => 0],
                                ['x' => 0.5, 'y' => 0.2 + ($index * 0.05)],
                                ['x' => 1, 'y' => 1],
                            ],
                        ],
                    ],
                );
            }
        }
    }
}
