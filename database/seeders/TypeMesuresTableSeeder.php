<?php

namespace Database\Seeders;

use App\Models\TypeMesure;
use Illuminate\Database\Seeder;

class TypeMesuresTableSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'poitrine', 'nom' => 'Tour de poitrine', 'unite' => 'cm', 'categorie' => 'principale', 'description' => 'Mesure clé pour robes, chemises et vestes.', 'est_actif' => true],
            ['code' => 'taille', 'nom' => 'Tour de taille', 'unite' => 'cm', 'categorie' => 'principale', 'description' => 'Mesure centrale pour robes, jupes et pantalons.', 'est_actif' => true],
            ['code' => 'hanche', 'nom' => 'Tour de hanches', 'unite' => 'cm', 'categorie' => 'principale', 'description' => 'Mesure indispensable pour jupes, pantalons et robes ajustées.', 'est_actif' => true],
            ['code' => 'epaule', 'nom' => 'Carrure épaule', 'unite' => 'cm', 'categorie' => 'principale', 'description' => 'Carrure d\'épaule pour hauts, chemises et boubous.', 'est_actif' => true],
            ['code' => 'longueur_robe', 'nom' => 'Longueur robe', 'unite' => 'cm', 'categorie' => 'secondaire', 'description' => 'Longueur totale d\'une robe ou kaba.', 'est_actif' => true],
            ['code' => 'longueur_manche', 'nom' => 'Longueur manche', 'unite' => 'cm', 'categorie' => 'secondaire', 'description' => 'Longueur de manche pour chemises, robes et vestes.', 'est_actif' => true],
            ['code' => 'tour_cou', 'nom' => 'Tour de cou', 'unite' => 'cm', 'categorie' => 'secondaire', 'description' => 'Mesure utile pour cols mao, chemises et tuniques.', 'est_actif' => true],
            ['code' => 'ceinture', 'nom' => 'Tour de ceinture', 'unite' => 'cm', 'categorie' => 'secondaire', 'description' => 'Aide au montage des ceintures et jupes hautes.', 'est_actif' => true],
            ['code' => 'entrejambe', 'nom' => 'Entrejambe', 'unite' => 'cm', 'categorie' => 'secondaire', 'description' => 'Mesure importante pour pantalons et ensembles.', 'est_actif' => true],
        ];

        foreach ($types as $t) {
            TypeMesure::query()->updateOrCreate(
                ['code' => $t['code']],
                [
                    'nom' => $t['nom'],
                    'unite' => $t['unite'],
                    'categorie' => $t['categorie'],
                    'description' => $t['description'],
                    'est_actif' => $t['est_actif'],
                ],
            );
        }
    }
}
