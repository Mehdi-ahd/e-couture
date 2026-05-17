<?php

namespace Database\Seeders;

use App\Models\TypeMesure;
use Illuminate\Database\Seeder;

class TypeMesuresTableSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'poitrine', 'nom' => 'Tour de poitrine', 'unite' => 'cm', 'categorie' => 'principale', 'description' => 'Tour de poitrine', 'est_actif' => true],
            ['code' => 'taille', 'nom' => 'Tour de taille', 'unite' => 'cm', 'categorie' => 'principale', 'description' => 'Tour de taille', 'est_actif' => true],
            ['code' => 'hanche', 'nom' => 'Tour de hanches', 'unite' => 'cm', 'categorie' => 'principale', 'description' => 'Tour de hanches', 'est_actif' => true],
            ['code' => 'epaule', 'nom' => 'Largeur d\'épaule', 'unite' => 'cm', 'categorie' => 'secondaire', 'description' => 'Largeur d\'épaule', 'est_actif' => true],
            ['code' => 'longueur', 'nom' => 'Longueur', 'unite' => 'cm', 'categorie' => 'secondaire', 'description' => 'Longueur du vêtement', 'est_actif' => true],
        ];

        foreach ($types as $t) {
            TypeMesure::query()->create([
                'code' => $t['code'],
                'nom' => $t['nom'],
                'unite' => $t['unite'],
                'categorie' => $t['categorie'],
                'description' => $t['description'],
                'est_actif' => $t['est_actif'],
            ]);
        }
    }
}
