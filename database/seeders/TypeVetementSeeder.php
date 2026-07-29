<?php

namespace Database\Seeders;

use App\Models\TypeVetement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TypeVetementSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            // ── HAUTS ──
            [
                'code' => 'TSHIRT',
                'nom' => 'T-shirts',
                'description' => 'T-shirts, polos et hauts décontractés à manches courtes ou longues.',
                'categorie' => 'Hauts',
                'genre' => 'mixte',
                'section' => 'adulte',
            ],
            [
                'code' => 'CHEMISE',
                'nom' => 'Chemises',
                'description' => 'Chemises classiques, cintrées, col mao, officier, hawaiiennes et en jean.',
                'categorie' => 'Hauts',
                'genre' => 'mixte',
                'section' => 'adulte',
            ],
        
            // ── BAS ──
            [
                'code' => 'PANTALON',
                'nom' => 'Pantalons',
                'description' => 'Pantalons droits, slim, regular, chino, cargo, à pinces, larges, palazzo et taille haute.',
                'categorie' => 'Bas',
                'genre' => 'mixte',
                'section' => 'adulte',
            ],
            [
                'code' => 'JEAN',
                'nom' => 'Jeans',
                'description' => 'Jeans droits, slim, skinny, bootcut, flare, boyfriend, mom et cargo.',
                'categorie' => 'Bas',
                'genre' => 'mixte',
                'section' => 'adulte',
            ],
            [
                'code' => 'JUPE',
                'nom' => 'Jupes',
                'description' => 'Jupes droites, crayon, trapèze, évasée, plissée, portefeuille, longue et courte.',
                'categorie' => 'Bas',
                'genre' => 'femme',
                'section' => 'adulte',
            ],
            [
                'code' => 'SHORT',
                'nom' => 'Shorts',
                'description' => 'Shorts classiques, cargo, bermuda, en jean et de sport.',
                'categorie' => 'Bas',
                'genre' => 'mixte',
                'section' => 'adulte',
            ],

            // ── PIÈCES UNIQUES ──
            [
                'code' => 'ROBE',
                'nom' => 'Robes',
                'description' => 'Robes droites, fourreau, portefeuille, trapèze, patineuse, chemise, longue, courte, bustier, empire, sirène, princesse, asymétrique et dos nu.',
                'categorie' => 'Pièces uniques',
                'genre' => 'femme',
                'section' => 'adulte',
            ],
            [
                'code' => 'COMBINAISON',
                'nom' => 'Combinaisons',
                'description' => 'Combinaisons pantalon, short, à manches, sans manches et salopettes.',
                'categorie' => 'Pièces uniques',
                'genre' => 'femme',
                'section' => 'adulte',
            ],

            // ── VÊTEMENTS D'EXTÉRIEUR ──
            [
                'code' => 'VESTE',
                'nom' => 'Vestes',
                'description' => 'Vestes classiques, blazers, vestes de costume, en jean, saharienne et militaire.',
                'categorie' => 'Vêtements d\'extérieur',
                'genre' => 'mixte',
                'section' => 'adulte',
            ],


            // ── TENUES TRADITIONNELLES ──
            [
                'code' => 'TENUE_TRADITIONNELLE',
                'nom' => 'Tenues traditionnelles',
                'description' => 'Grand boubou, agbada, caftan, ensembles africains homme/femme, tenues yoruba, fon et de cérémonie en bazin.',
                'categorie' => 'Pièces uniques',
                'genre' => 'mixte',
                'section' => 'adulte',
            ],
        ];

        foreach ($types as $type) {
            TypeVetement::create(array_merge($type, [
                'external_id' => (string) Str::uuid(),
            ]));
        }
    }
}
