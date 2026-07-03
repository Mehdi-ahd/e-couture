<?php

namespace Database\Seeders;

use App\Models\Materiau;
use App\Models\ModeleVetement;
use App\Models\TypeVetement;
use App\Models\User;
use Illuminate\Database\Seeder;

class ModeleVetementsTableSeeder extends Seeder
{
    public function run(): void
    {
        $types = TypeVetement::query()->pluck('id', 'code');
        $firstCouturier = User::query()->role(User::ROLE_COUTURIER)->orderBy('id')->first();
        $materials = Materiau::query()->pluck('id', 'nom');

        $models = [
            [
                'nom' => 'Robe kaba ajustée',
                'description' => 'Modèle public inspiré des robes kaba portées pour les sorties, cérémonies et rendez-vous en ville.',
                'type_code' => 'robe',
                'portee' => 'public',
                'statut' => 'publie',
                'prestataire_id' => 2,
                'materials' => ['Wax hollandais', 'Doublure légère'],
            ],
            [
                'nom' => 'Ensemble wax Cotonou',
                'description' => 'Ensemble haut et pantalon en wax pour clientèle urbaine et active.',
                'type_code' => 'ensemble',
                'portee' => 'public',
                'statut' => 'publie',
                'prestataire_id' => 3,
                'materials' => ['Wax hollandais', 'Popeline blanche'],
            ],
            [
                'nom' => 'Boubou homme Porto-Novo',
                'description' => 'Boubou homme de cérémonie avec coupe ample et finitions propres.',
                'type_code' => 'boubou',
                'portee' => 'public',
                'statut' => 'publie',
                'prestataire_id' => null,
                'materials' => ['Bazin riche bleu nuit', 'Brocart léger'],
            ],
            [
                'nom' => 'Chemise africaine col mao',
                'description' => 'Chemise polyvalente inspirée des tenues modernes du quotidien au Bénin.',
                'type_code' => 'chemise',
                'portee' => 'public',
                'statut' => 'publie',
                'prestataire_id' => null,
                'materials' => ['Popeline blanche', 'Lin léger naturel'],
            ],
            [
                'nom' => 'Jupe taille haute wax',
                'description' => 'Jupe droite ou légèrement évasée, pensée pour les tissus wax et les morphologies variées.',
                'type_code' => 'jupe',
                'portee' => 'public',
                'statut' => 'publie',
                'prestataire_id' => null,
                'materials' => ['Wax hollandais', 'Doublure légère'],
            ],
            [
                'nom' => 'Tenue sortie de mairie',
                'description' => 'Création atelier privée pour essayage et personnalisation rapide.',
                'type_code' => 'ensemble',
                'portee' => 'prive',
                'statut' => 'brouillon',
                'prestataire_id' => $firstCouturier?->id,
                'materials' => ['Dentelle guipure ivoire', 'Satin duchesse'],
            ],
        ];

        foreach ($models as $definition) {
            $typeId = $types[$definition['type_code']] ?? null;
            if ($typeId === null) {
                continue;
            }

            $model = ModeleVetement::query()->updateOrCreate(
                [
                    'nom' => $definition['nom'],
                    'prestataire_id' => $definition['prestataire_id'],
                ],
                [
                    'description' => $definition['description'],
                    'portee' => $definition['portee'],
                    'statut' => $definition['statut'],
                    'type_vetement_id' => $typeId,
                ],
            );

            $materialIds = collect($definition['materials'])
                ->map(fn (string $name) => $materials[$name] ?? null)
                ->filter()
                ->values()
                ->all();

            if ($materialIds !== []) {
                $model->materiaux()->syncWithoutDetaching($materialIds);
            }
        }
    }
}
