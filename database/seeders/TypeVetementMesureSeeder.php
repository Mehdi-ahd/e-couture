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
            'TSHIRT' => ['HAUTEUR', 'TORSE', 'MANCHE_LONGUE', 'MANCHE_COURTE', 'BRA_AV', 'EPAULES', 'POITRINE', 'CEINTURE', 'CARRURE_DEVANT', 'CARRURE_DOS'],
            'CHEMISE' => ['HAUTEUR', 'TORSE', 'MANCHE_LONGUE', 'MANCHE_COURTE', 'BRA_AV', 'EPAULES', 'POITRINE', 'CEINTURE', 'TOUR_COU', 'TOUR_POIGNET', 'CARRURE_DEVANT', 'CARRURE_DOS', 'LONGUEUR_CHEMISE'],
            'PANTALON' => ['HAUTEUR', 'JAMBE', 'CUISSE', 'MOLLET', 'CEINTURE', 'TOUR_FESSES', 'HANCHES_L', 'TOUR_GENOU', 'HAUTEUR_GENOU', 'TOUR_BAS'],
            'JEAN' => ['HAUTEUR', 'JAMBE', 'CUISSE', 'MOLLET', 'CEINTURE', 'TOUR_FESSES', 'HANCHES_L', 'TOUR_GENOU', 'HAUTEUR_GENOU', 'TOUR_BAS'],
            'JUPE' => ['HAUTEUR', 'JAMBE', 'CEINTURE', 'TOUR_FESSES', 'HANCHES_L', 'LONGUEUR_JUPE', 'HAUTEUR_GENOU'],
            'SHORT' => ['HAUTEUR', 'CUISSE', 'CEINTURE', 'TOUR_FESSES', 'HANCHES_L'],
            'ROBE' => ['HAUTEUR', 'TORSE', 'MANCHE_LONGUE', 'MANCHE_COURTE', 'BRA_AV', 'JAMBE', 'EPAULES', 'POITRINE', 'CEINTURE', 'TOUR_FESSES', 'HANCHES_L', 'TOUR_COU', 'TOUR_POIGNET', 'LONGUEUR_ROBE', 'HAUT_SEIN', 'CARRURE_DEVANT', 'CARRURE_DOS'],
            'COMBINAISON' => ['HAUTEUR', 'TORSE', 'MANCHE_LONGUE', 'MANCHE_COURTE', 'BRA_AV', 'JAMBE', 'CUISSE', 'MOLLET', 'EPAULES', 'POITRINE', 'CEINTURE', 'TOUR_FESSES', 'HANCHES_L', 'TOUR_COU', 'TOUR_POIGNET', 'TOUR_GENOU', 'HAUTEUR_GENOU', 'CARRURE_DEVANT', 'CARRURE_DOS'],
            'VESTE' => ['HAUTEUR', 'TORSE', 'MANCHE_LONGUE', 'MANCHE_COURTE', 'BRA_AV', 'EPAULES', 'POITRINE', 'CEINTURE', 'TOUR_COU', 'TOUR_POIGNET', 'CARRURE_DEVANT', 'CARRURE_DOS', 'LONGUEUR_CHEMISE'],
            'TENUE_TRADITIONNELLE' => ['HAUTEUR', 'TORSE', 'MANCHE_LONGUE', 'MANCHE_COURTE', 'BRA_AV', 'EPAULES', 'POITRINE', 'CEINTURE', 'TOUR_FESSES', 'HANCHES_L', 'TOUR_COU', 'TOUR_POIGNET', 'CARRURE_DEVANT', 'CARRURE_DOS'],
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
