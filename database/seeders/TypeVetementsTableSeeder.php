<?php

namespace Database\Seeders;

use App\Models\TypeVetement;
use Illuminate\Database\Seeder;

class TypeVetementsTableSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'robe', 'nom' => 'Robe', 'description' => 'Robes de ville, de cérémonie et tenues sur mesure.', 'est_actif' => true],
            ['code' => 'ensemble', 'nom' => 'Ensemble', 'description' => 'Ensembles haut et bas pour atelier et cérémonies.', 'est_actif' => true],
            ['code' => 'boubou', 'nom' => 'Boubou', 'description' => 'Boubous homme et femme inspirés des usages ouest-africains.', 'est_actif' => true],
            ['code' => 'chemise', 'nom' => 'Chemise', 'description' => 'Chemises africaines, casual et cérémonie.', 'est_actif' => true],
            ['code' => 'jupe', 'nom' => 'Jupe', 'description' => 'Jupes taille haute, droites ou évasées.', 'est_actif' => true],
            ['code' => 'pantalon', 'nom' => 'Pantalon', 'description' => 'Pantalons ville, coupe cigarette ou ample.', 'est_actif' => true],
            ['code' => 'veste', 'nom' => 'Veste', 'description' => 'Vestes et survestes tailleur.', 'est_actif' => true],
            ['code' => 'tunique', 'nom' => 'Tunique', 'description' => 'Tuniques fluides et tenues légères du quotidien.', 'est_actif' => true],
        ];

        foreach ($types as $t) {
            TypeVetement::query()->updateOrCreate(
                ['code' => $t['code']],
                [
                    'nom' => $t['nom'],
                    'description' => $t['description'],
                    'est_actif' => $t['est_actif'],
                ],
            );
        }
    }
}
