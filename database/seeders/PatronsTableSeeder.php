<?php

namespace Database\Seeders;

use App\Models\ModeleVetement;
use App\Models\Patron;
use Illuminate\Database\Seeder;

class PatronsTableSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            'Robe kaba ajustée' => ['methode' => 'manuel', 'version' => 2, 'statut' => 'valide'],
            'Ensemble wax Cotonou' => ['methode' => 'manuel', 'version' => 1, 'statut' => 'valide'],
            'Boubou homme Porto-Novo' => ['methode' => 'manuel', 'version' => 1, 'statut' => 'valide'],
            'Chemise africaine col mao' => ['methode' => 'genere', 'version' => 1, 'statut' => 'valide'],
            'Jupe taille haute wax' => ['methode' => 'manuel', 'version' => 1, 'statut' => 'valide'],
            'Tenue sortie de mairie' => ['methode' => 'manuel', 'version' => 1, 'statut' => 'brouillon'],
        ];

        foreach ($definitions as $modelName => $definition) {
            $model = ModeleVetement::query()->where('nom', $modelName)->first();
            if ($model === null) {
                continue;
            }

            Patron::query()->updateOrCreate(
                ['modele_vetement_id' => $model->id],
                [
                    'methode' => $definition['methode'],
                    'version' => $definition['version'],
                    'statut' => $definition['statut'],
                    'donnees_dessin' => ['seed' => 'benin_2026', 'model' => $modelName],
                ],
            );
        }
    }
}
