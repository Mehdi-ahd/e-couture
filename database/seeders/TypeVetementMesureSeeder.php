<?php

namespace Database\Seeders;

use App\Models\TypeMesure;
use App\Models\TypeVetement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeVetementMesureSeeder extends Seeder
{
    public function run(): void
    {
        $typeMesures = TypeMesure::pluck('id', 'code');
        $typeVetements = TypeVetement::pluck('id', 'code');

        $associations = [
            'TSHIRT' => ['HAUTEUR', 'TORSE', 'BRA_TOTAL', 'BRA_HAUT', 'BRA_AV', 'EPAULES', 'POITRINE', 'TAILLE'],
            'CHEMISE' => ['HAUTEUR', 'TORSE', 'BRA_TOTAL', 'BRA_HAUT', 'BRA_AV', 'EPAULES', 'POITRINE', 'TAILLE', 'TOUR_COU', 'TOUR_POIGNET'],
            'PULL' => ['HAUTEUR', 'TORSE', 'BRA_TOTAL', 'BRA_HAUT', 'BRA_AV', 'EPAULES', 'POITRINE', 'TAILLE', 'TOUR_POIGNET'],
            'DEBARDEUR' => ['HAUTEUR', 'TORSE', 'EPAULES', 'POITRINE', 'TAILLE'],
            'PANTALON' => ['HAUTEUR', 'JAMBE', 'CUISSE', 'MOLLET', 'TAILLE', 'TOUR_HANCHES', 'HANCHES_L', 'TOUR_GENOU'],
            'JEAN' => ['HAUTEUR', 'JAMBE', 'CUISSE', 'MOLLET', 'TAILLE', 'TOUR_HANCHES', 'HANCHES_L', 'TOUR_GENOU'],
            'JUPE' => ['HAUTEUR', 'JAMBE', 'TAILLE', 'TOUR_HANCHES', 'HANCHES_L'],
            'SHORT' => ['HAUTEUR', 'CUISSE', 'TAILLE', 'TOUR_HANCHES', 'HANCHES_L'],
            'ROBE' => ['HAUTEUR', 'TORSE', 'BRA_TOTAL', 'BRA_HAUT', 'BRA_AV', 'JAMBE', 'EPAULES', 'POITRINE', 'TAILLE', 'TOUR_HANCHES', 'HANCHES_L', 'TOUR_COU', 'TOUR_POIGNET'],
            'COMBINAISON' => ['HAUTEUR', 'TORSE', 'BRA_TOTAL', 'BRA_HAUT', 'BRA_AV', 'JAMBE', 'CUISSE', 'MOLLET', 'EPAULES', 'POITRINE', 'TAILLE', 'TOUR_HANCHES', 'HANCHES_L', 'TOUR_COU', 'TOUR_POIGNET', 'TOUR_GENOU'],
            'MANTEAU' => ['HAUTEUR', 'TORSE', 'BRA_TOTAL', 'BRA_HAUT', 'BRA_AV', 'EPAULES', 'POITRINE', 'TAILLE', 'TOUR_COU', 'TOUR_POIGNET'],
            'VESTE' => ['HAUTEUR', 'TORSE', 'BRA_TOTAL', 'BRA_HAUT', 'BRA_AV', 'EPAULES', 'POITRINE', 'TAILLE', 'TOUR_COU', 'TOUR_POIGNET'],
            'BLOUSON' => ['HAUTEUR', 'TORSE', 'BRA_TOTAL', 'BRA_HAUT', 'BRA_AV', 'EPAULES', 'POITRINE', 'TAILLE', 'TOUR_COU', 'TOUR_POIGNET'],
            'DOUDOUNE' => ['HAUTEUR', 'TORSE', 'BRA_TOTAL', 'BRA_HAUT', 'BRA_AV', 'EPAULES', 'POITRINE', 'TAILLE', 'TOUR_COU', 'TOUR_POIGNET'],
            'SOUS_VETEMENT' => ['POITRINE', 'TAILLE', 'TOUR_HANCHES'],
            'TENUE_TRADITIONNELLE' => ['HAUTEUR', 'TORSE', 'BRA_TOTAL', 'BRA_HAUT', 'BRA_AV', 'EPAULES', 'POITRINE', 'TAILLE', 'TOUR_HANCHES', 'HANCHES_L', 'TOUR_COU', 'TOUR_POIGNET'],
        ];

        $rows = [];

        foreach ($associations as $typeCode => $mesureCodes) {
            $typeVetementId = $typeVetements[$typeCode] ?? null;
            if (! $typeVetementId) {
                continue;
            }

            foreach ($mesureCodes as $mesureCode) {
                $typeMesureId = $typeMesures[$mesureCode] ?? null;
                if (! $typeMesureId) {
                    continue;
                }

                $rows[] = [
                    'type_vetement_id' => $typeVetementId,
                    'type_mesure_id' => $typeMesureId,
                    'est_obligatoire' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('type_vetement_mesures')->insert($rows);
    }
}
