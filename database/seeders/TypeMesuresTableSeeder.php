<?php

namespace Database\Seeders;

use App\Models\TypeMesure;
use Illuminate\Database\Seeder;

class TypeMesuresTableSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'HAUTEUR',      'nom' => 'Hauteur totale',               'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Hauteur estimée à partir des repères corporels.'],
            ['code' => 'EPAULES',      'nom' => 'Largeur épaules',              'unite' => 'cm', 'categorie' => 'largeur',      'description' => 'Carrure des épaules (face + dos fusionnés).'],
            ['code' => 'TORSE',        'nom' => 'Longueur torse',               'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Longueur entre les épaules et la taille/hanche.'],
            ['code' => 'BRA_TOTAL',    'nom' => 'Longueur bras total',          'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Longueur de l\'épaule au poignet.'],
            ['code' => 'BRA_HAUT',     'nom' => 'Haut du bras',                 'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Longueur de l\'épaule au coude.'],
            ['code' => 'BRA_AV',       'nom' => 'Avant-bras',                   'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Longueur du coude au poignet.'],
            ['code' => 'JAMBE',        'nom' => 'Longueur jambe',               'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Longueur de la hanche à la cheville.'],
            ['code' => 'CUISSE',       'nom' => 'Longueur cuisse',              'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Longueur de la hanche au genou.'],
            ['code' => 'MOLLET',       'nom' => 'Longueur mollet',              'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Longueur du genou à la cheville.'],
            ['code' => 'HANCHES_L',    'nom' => 'Largeur hanches',              'unite' => 'cm', 'categorie' => 'largeur',      'description' => 'Largeur du bassin au niveau des hanches.'],
            ['code' => 'POITRINE',     'nom' => 'Tour de poitrine',             'unite' => 'cm', 'categorie' => 'circonference', 'description' => 'Tour de poitrine estimé par ellipse ou ratio.'],
            ['code' => 'TAILLE',       'nom' => 'Tour de taille',               'unite' => 'cm', 'categorie' => 'circonference', 'description' => 'Tour de taille estimé par ellipse ou ratio.'],
            ['code' => 'TOUR_HANCHES', 'nom' => 'Tour de hanches',              'unite' => 'cm', 'categorie' => 'circonference', 'description' => 'Tour de hanches estimé par ellipse ou ratio.'],
            ['code' => 'TOUR_COU',     'nom' => 'Tour de cou',                  'unite' => 'cm', 'categorie' => 'circonference', 'description' => 'Tour de cou estimé par ratio.'],
            ['code' => 'TOUR_GENOU',   'nom' => 'Tour de genou',                'unite' => 'cm', 'categorie' => 'circonference', 'description' => 'Tour de genou estimé par ratio.'],
            ['code' => 'TOUR_POIGNET', 'nom' => 'Tour de poignet',              'unite' => 'cm', 'categorie' => 'circonference', 'description' => 'Tour de poignet estimé par ratio.'],
        ];

        foreach ($types as $t) {
            TypeMesure::query()->updateOrCreate(
                ['code' => $t['code']],
                [
                    'nom' => $t['nom'],
                    'unite' => $t['unite'],
                    'categorie' => $t['categorie'],
                    'description' => $t['description'],
                    'est_actif' => $t['est_actif'] ?? true,
                ],
            );
        }
    }
}
