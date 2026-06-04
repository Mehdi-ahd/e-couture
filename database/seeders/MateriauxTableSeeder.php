<?php

namespace Database\Seeders;

use App\Models\FormeDecoupe;
use App\Models\Materiau;
use Illuminate\Database\Seeder;

class MateriauxTableSeeder extends Seeder
{
    public function run(): void
    {
        $shapeIds = FormeDecoupe::query()->pluck('id', 'nom');

        $items = [
            ['nom' => 'Wax hollandais', 'description' => 'Coton imprimé très utilisé pour tenues du quotidien et ensembles de sortie.', 'type' => 'coton_imprime', 'forme' => 'Coupe droite'],
            ['nom' => 'Bazin riche bleu nuit', 'description' => 'Bazin de cérémonie prisé pour boubous, ensembles habillés et grands événements.', 'type' => 'bazin', 'forme' => 'Coupe cintrée'],
            ['nom' => 'Popeline blanche', 'description' => 'Tissu propre et stable pour chemises, doublures légères et détails de finition.', 'type' => 'popeline', 'forme' => 'Manche montée'],
            ['nom' => 'Lin léger naturel', 'description' => 'Lin respirant adapté aux tenues sobres et à la chaleur de Cotonou ou Porto-Novo.', 'type' => 'lin', 'forme' => 'Coupe droite'],
            ['nom' => 'Dentelle guipure ivoire', 'description' => 'Matière de cérémonie pour sorties de mairie, réceptions et robes d’apparat.', 'type' => 'dentelle', 'forme' => 'Pince poitrine'],
            ['nom' => 'Satin duchesse', 'description' => 'Matière souple pour doublure, ceinture ou robe de fête.', 'type' => 'satin', 'forme' => 'Encolure ronde'],
            ['nom' => 'Doublure légère', 'description' => 'Doublure fine pour améliorer le confort et la finition intérieure.', 'type' => 'doublure', 'forme' => 'Coupe droite'],
            ['nom' => 'Brocart léger', 'description' => 'Tissu texturé pour empiècements, cols et finitions décoratives.', 'type' => 'brocart', 'forme' => 'Encolure V'],
        ];

        foreach ($items as $item) {
            Materiau::query()->updateOrCreate(
                ['nom' => $item['nom']],
                [
                    'description' => $item['description'],
                    'type' => $item['type'],
                    'est_global' => true,
                    'forme_decoupe_id' => $shapeIds[$item['forme']] ?? null,
                ],
            );
        }
    }
}
