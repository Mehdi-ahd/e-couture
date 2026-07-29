<?php

namespace Database\Seeders;

use App\Models\TypeMesure;
use Illuminate\Database\Seeder;

class TypeMesuresTableSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'HAUTEUR',        'nom' => 'Hauteur totale',              'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Hauteur estimée à partir des repères corporels.'],
            ['code' => 'EPAULES',        'nom' => 'Largeur épaules',             'unite' => 'cm', 'categorie' => 'largeur',      'description' => 'Carrure des épaules (face + dos fusionnés).'],
            ['code' => 'TORSE',          'nom' => 'Longueur torse',              'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Longueur entre les épaules et la taille/hanche.'],
            ['code' => 'MANCHE_LONGUE',  'nom' => 'Longueur manche longue',      'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Longueur de l\'épaule au poignet.'],
            ['code' => 'MANCHE_COURTE',  'nom' => 'Longueur manche courte',      'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Longueur de l\'épaule au coude.'],
            ['code' => 'BRA_AV',         'nom' => 'Avant-bras',                  'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Longueur du coude au poignet.'],
            ['code' => 'JAMBE',          'nom' => 'Longueur jambe',              'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Longueur de la hanche à la cheville.'],
            ['code' => 'CUISSE',         'nom' => 'Longueur cuisse',             'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Longueur de la hanche au genou.'],
            ['code' => 'MOLLET',         'nom' => 'Longueur mollet',             'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Longueur du genou à la cheville.'],
            ['code' => 'HANCHES_L',      'nom' => 'Largeur hanches',             'unite' => 'cm', 'categorie' => 'largeur',      'description' => 'Largeur du bassin au niveau des hanches.'],
            ['code' => 'POITRINE',       'nom' => 'Tour de poitrine',            'unite' => 'cm', 'categorie' => 'circonference', 'description' => 'Tour de poitrine estimé par ellipse ou ratio.'],
            ['code' => 'CEINTURE',       'nom' => 'Tour de ceinture',            'unite' => 'cm', 'categorie' => 'circonference', 'description' => 'Tour de ceinture estimé par ellipse ou ratio.'],
            ['code' => 'TOUR_FESSES',    'nom' => 'Tour de fesses',              'unite' => 'cm', 'categorie' => 'circonference', 'description' => 'Tour de fesses estimé par ellipse ou ratio.'],
            ['code' => 'TOUR_COU',       'nom' => 'Tour de cou',                 'unite' => 'cm', 'categorie' => 'circonference', 'description' => 'Tour de cou estimé par ratio.'],
            ['code' => 'TOUR_GENOU',     'nom' => 'Tour de genou',               'unite' => 'cm', 'categorie' => 'circonference', 'description' => 'Tour de genou estimé par ratio.'],
            ['code' => 'TOUR_POIGNET',   'nom' => 'Tour de poignet',             'unite' => 'cm', 'categorie' => 'circonference', 'description' => 'Tour de poignet estimé par ratio.'],
            ['code' => 'HAUT_SEIN',      'nom' => 'Hauteur de sein',             'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Distance épaule → niveau buste (ratio torse).'],
            ['code' => 'LONGUEUR_TAILLE', 'nom' => 'Longueur taille',             'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Distance épaule → nombril.'],
            ['code' => 'LONGUEUR_CHEMISE', 'nom' => 'Longueur chemise',           'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Distance nombril → hanche.'],
            ['code' => 'LONGUEUR_JUPE',  'nom' => 'Longueur jupe longue',        'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Distance hanche → cheville.'],
            ['code' => 'LONGUEUR_ROBE',  'nom' => 'Longueur robe',               'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Distance épaule → cheville.'],
            ['code' => 'HAUTEUR_GENOU',  'nom' => 'Hauteur genou',               'unite' => 'cm', 'categorie' => 'longueur',     'description' => 'Distance sol → genou (talon + bonus 6 cm).'],
            ['code' => 'CARRURE_DEVANT', 'nom' => 'Carrure devant',              'unite' => 'cm', 'categorie' => 'largeur',      'description' => 'Largeur entre les emmanchures côté face.'],
            ['code' => 'CARRURE_DOS',    'nom' => 'Carrure dos',                 'unite' => 'cm', 'categorie' => 'largeur',      'description' => 'Largeur entre les emmanchures côté dos.'],
            ['code' => 'TOUR_BAS',       'nom' => 'Tour du bas (cheville)',      'unite' => 'cm', 'categorie' => 'circonference', 'description' => 'Périmètre de la cheville estimé par ratio.'],
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
