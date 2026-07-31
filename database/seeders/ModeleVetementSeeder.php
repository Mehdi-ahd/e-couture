<?php

namespace Database\Seeders;

use App\Models\ModeleVetement;
use App\Models\TypeVetement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ModeleVetementSeeder extends Seeder
{
    public function run(): void
    {
        $typeId = fn (string $code) => TypeVetement::where('code', $code)->value('id');

        $images = [
            'TSHIRT_COL_ROND'          => 'storage/images_modeles/TSHIRT_COL_ROND.png',
            'TSHIRT_COL_V'             => 'storage/images_modeles/TSHIRT_COL_V.jpg',
            'TSHIRT_MANCHES_LONGUES'   => 'storage/images_modeles/TSHIRT_MANCHES_LONGUES.jpg',
            'POLO'                     => 'storage/images_modeles/POLO.jpg',
            'CHEMISE_MANCHES_COURTES'  => 'storage/images_modeles/CHEMISE_MANCHES_COURTES.jpg',
            'CHEMISE_MANCHES_LONGUES'  => 'storage/images_modeles/CHEMISE_MANCHES_LONGUES.jpg',
            'CHEMISE_COL_MAO'          => 'storage/images_modeles/CHEMISE_COL_MAO.jpg',
            'CHEMISE_JEAN'             => 'storage/images_modeles/CHEMISE_JEAN.jpg',
            'CHEMISE_WAX'              => 'storage/images_modeles/CHEMISE_WAX.jpeg',
            'CHEMISE_AFRICAINE_BRODEE' => 'storage/images_modeles/CHEMISE_AFRICAINE_BRODEE.jpg',
            'CARGO'                    => 'storage/images_modeles/CARGO.jpg',
            'PANTALON_PALAZZO'         => 'storage/images_modeles/PANTALON_PALAZZO.jpg',
            'PANTALON_TAILLE_HAUTE'    => 'storage/images_modeles/PANTALON_TAILLE_HAUTE.jpg',
            'JEAN_DROIT'               => 'storage/images_modeles/JEAN_DROIT.jpg',
            'JEAN_BOOTCUT'             => 'storage/images_modeles/JEAN_BOOTCUT.jpg',
            'JEAN_CARGO'               => 'storage/images_modeles/JEAN_CARGO.jpg',
            'JUPE_DROITE'              => 'storage/images_modeles/JUPE_DROITE.jpg',
            'JUPE_CRAYON'              => 'storage/images_modeles/JUPE_CRAYON.jpg',
            'JUPE_EVASEE'              => 'storage/images_modeles/JUPE_EVASEE.jpg',
            'JUPE_PLISSEE'             => 'storage/images_modeles/JUPE_PLISSEE.jpg',
            'JUPE_PORTEFEUILLE'        => 'storage/images_modeles/JUPE_PORTEFEUILLE.jpg',
            'SHORT_CLASSIQUE'          => 'storage/images_modeles/SHORT_CLASSIQUE.jpg',
            'SHORT_CARGO'              => 'storage/images_modeles/SHORT_CARGO.jpg',
            'ROBE_DROITE'              => 'storage/images_modeles/ROBE_DROITE.jpeg',
            'ROBE_FOURREAU'            => 'storage/images_modeles/ROBE_FOURREAU.jpg',
            'ROBE_DOS_NU'              => 'storage/images_modeles/ROBE_DOS_NU.jpg',
            'ROBE_WAX'                 => 'storage/images_modeles/ROBE_WAX.jpg',
            'ROBE_KABA'                => 'storage/images_modeles/ROBE_KABA.png',
            'ROBE_AFRICAINE_LONGUE'    => 'storage/images_modeles/ROBE_AFRICAINE_LONGUE.jpg',
            'VESTE_CLASSIQUE'          => 'storage/images_modeles/VESTE_CLASSIQUE.jpg',
            'VESTE_JEAN'               => 'storage/images_modeles/VESTE_JEAN.jpg',
            'VESTE_AFRICAINE_BRODEE'   => 'storage/images_modeles/VESTE_AFRICAINE_BRODEE.jpg',
            'GRAND_BOUBOU'             => 'storage/images_modeles/GRAND_BOUBOU.jpg',
            'AGBADA'                   => 'storage/images_modeles/AGBADA.png',
            'CAFTAN'                   => 'storage/images_modeles/CAFTAN.jpg',
        ];

        $modeles = [
            // ── T-SHIRTS ──
            ['code' => 'TSHIRT_COL_ROND', 'type_vetement_code' => 'TSHIRT', 'nom' => 'T-shirt col rond'],
            ['code' => 'TSHIRT_COL_V', 'type_vetement_code' => 'TSHIRT', 'nom' => 'T-shirt col V'],
            ['code' => 'TSHIRT_MANCHES_LONGUES', 'type_vetement_code' => 'TSHIRT', 'nom' => 'T-shirt manches longues'],
            ['code' => 'POLO', 'type_vetement_code' => 'TSHIRT', 'nom' => 'Polo'],

            // ── CHEMISES ──
            //['code' => 'CHEMISE_CLASSIQUE', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise classique'],
            //['code' => 'CHEMISE_CINTREE', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise cintrée'],
            ['code' => 'CHEMISE_MANCHES_COURTES', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise manches courtes'],
            ['code' => 'CHEMISE_MANCHES_LONGUES', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise manches longues'],
            ['code' => 'CHEMISE_COL_MAO', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise col mao'],
            //['code' => 'CHEMISE_COL_OFFICIER', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise col officier'],
            //['code' => 'CHEMISE_HAWAIENNE', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise hawaïenne'],
            ['code' => 'CHEMISE_JEAN', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise en jean'],

            // ── CHEMISES TRADITIONNELLES ──
            ['code' => 'CHEMISE_WAX', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise en tissu wax'],
            ['code' => 'CHEMISE_AFRICAINE_BRODEE', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise africaine brodée'],
            //['code' => 'CHEMISE_BAZIN', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise en bazin'],

            // ── PANTALONS ──
            ['code' => 'PANTALON_DROIT', 'type_vetement_code' => 'PANTALON', 'nom' => 'Pantalon droit'],
            // ['code' => 'PANTALON_SLIM', 'type_vetement_code' => 'PANTALON', 'nom' => 'Pantalon slim'],
            // ['code' => 'PANTALON_REGULAR', 'type_vetement_code' => 'PANTALON', 'nom' => 'Pantalon regular'],
            // ['code' => 'CHINO', 'type_vetement_code' => 'PANTALON', 'nom' => 'Chino'],
            ['code' => 'CARGO', 'type_vetement_code' => 'PANTALON', 'nom' => 'Cargo'],
            // ['code' => 'PANTALON_PINCES', 'type_vetement_code' => 'PANTALON', 'nom' => 'Pantalon à pinces'],
            // ['code' => 'PANTALON_LARGE', 'type_vetement_code' => 'PANTALON', 'nom' => 'Pantalon large'],
            ['code' => 'PANTALON_PALAZZO', 'type_vetement_code' => 'PANTALON', 'nom' => 'Pantalon palazzo'],
            ['code' => 'PANTALON_TAILLE_HAUTE', 'type_vetement_code' => 'PANTALON', 'nom' => 'Pantalon taille haute'],

            // ── PANTALONS TRADITIONNELS ──
            //['code' => 'PANTALON_AFRICAIN_DROIT', 'type_vetement_code' => 'PANTALON', 'nom' => 'Pantalon africain droit'],
            //['code' => 'PANTALON_BAZIN', 'type_vetement_code' => 'PANTALON', 'nom' => 'Pantalon en bazin'],
            //['code' => 'PANTALON_TRADITIONNEL_BRODE', 'type_vetement_code' => 'PANTALON', 'nom' => 'Pantalon traditionnel brodé'],

            // ── JEANS ──
            ['code' => 'JEAN_DROIT', 'type_vetement_code' => 'JEAN', 'nom' => 'Jean droit'],
            //['code' => 'JEAN_SLIM', 'type_vetement_code' => 'JEAN', 'nom' => 'Jean slim'],
            //['code' => 'JEAN_SKINNY', 'type_vetement_code' => 'JEAN', 'nom' => 'Jean skinny'],
            ['code' => 'JEAN_BOOTCUT', 'type_vetement_code' => 'JEAN', 'nom' => 'Jean bootcut'],
            //['code' => 'JEAN_FLARE', 'type_vetement_code' => 'JEAN', 'nom' => 'Jean flare'],
            //['code' => 'JEAN_BOYFRIEND', 'type_vetement_code' => 'JEAN', 'nom' => 'Jean boyfriend'],
            //['code' => 'JEAN_MOM', 'type_vetement_code' => 'JEAN', 'nom' => 'Jean mom'],
            ['code' => 'JEAN_CARGO', 'type_vetement_code' => 'JEAN', 'nom' => 'Jean cargo'],

            // ── JUPES ──
            ['code' => 'JUPE_DROITE', 'type_vetement_code' => 'JUPE', 'nom' => 'Jupe droite'],
            ['code' => 'JUPE_CRAYON', 'type_vetement_code' => 'JUPE', 'nom' => 'Jupe crayon'],
            //['code' => 'JUPE_TRAPEZE', 'type_vetement_code' => 'JUPE', 'nom' => 'Jupe trapèze'],
            ['code' => 'JUPE_EVASEE', 'type_vetement_code' => 'JUPE', 'nom' => 'Jupe évasée'],
            ['code' => 'JUPE_PLISSEE', 'type_vetement_code' => 'JUPE', 'nom' => 'Jupe plissée'],
            ['code' => 'JUPE_PORTEFEUILLE', 'type_vetement_code' => 'JUPE', 'nom' => 'Jupe portefeuille'],
            // ['code' => 'JUPE_LONGUE', 'type_vetement_code' => 'JUPE', 'nom' => 'Jupe longue'],
            // ['code' => 'JUPE_COURTE', 'type_vetement_code' => 'JUPE', 'nom' => 'Jupe courte'],

            // ── SHORTS ──
            ['code' => 'SHORT_CLASSIQUE', 'type_vetement_code' => 'SHORT', 'nom' => 'Short classique'],
            ['code' => 'SHORT_CARGO', 'type_vetement_code' => 'SHORT', 'nom' => 'Short cargo'],
            // ['code' => 'BERMUDA', 'type_vetement_code' => 'SHORT', 'nom' => 'Bermuda'],
            // ['code' => 'SHORT_JEAN', 'type_vetement_code' => 'SHORT', 'nom' => 'Short en jean'],
            // ['code' => 'SHORT_SPORT', 'type_vetement_code' => 'SHORT', 'nom' => 'Short de sport'],

            // ── ROBES ──
            ['code' => 'ROBE_DROITE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe droite'],
            ['code' => 'ROBE_FOURREAU', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe fourreau'],
            // ['code' => 'ROBE_PORTEFEUILLE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe portefeuille'],
            // ['code' => 'ROBE_TRAPEZE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe trapèze'],
            // ['code' => 'ROBE_PATINEUSE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe patineuse'],
            // ['code' => 'ROBE_CHEMISE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe chemise'],
            // ['code' => 'ROBE_LONGUE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe longue'],
            // ['code' => 'ROBE_COURTE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe courte'],
            // ['code' => 'ROBE_BUSTIER', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe bustier'],
            // ['code' => 'ROBE_EMPIRE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe empire'],
            // ['code' => 'ROBE_SIRENE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe sirène'],
            // ['code' => 'ROBE_PRINCESSE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe princesse'],
            // ['code' => 'ROBE_ASYMETRIQUE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe asymétrique'],
            ['code' => 'ROBE_DOS_NU', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe dos nu'],

            // ── ROBES TRADITIONNELLES ──
            ['code' => 'ROBE_WAX', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe en wax'],
            ['code' => 'ROBE_KABA', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe kaba'],
            ['code' => 'ROBE_AFRICAINE_LONGUE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe africaine longue'],
            // ['code' => 'ROBE_AFRICAINE_CINTREE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe africaine cintrée'],
            // ['code' => 'ROBE_CEREMONIE_BAZIN', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe de cérémonie en bazin'],

            // ── COMBINAISONS ──
            ['code' => 'COMBI_PANTALON', 'type_vetement_code' => 'COMBINAISON', 'nom' => 'Combinaison pantalon'],
            ['code' => 'COMBI_SHORT', 'type_vetement_code' => 'COMBINAISON', 'nom' => 'Combinaison short'],
            ['code' => 'COMBI_MANCHES', 'type_vetement_code' => 'COMBINAISON', 'nom' => 'Combinaison à manches'],
            ['code' => 'COMBI_SANS_MANCHES', 'type_vetement_code' => 'COMBINAISON', 'nom' => 'Combinaison sans manches'],
            ['code' => 'SALOPETTE', 'type_vetement_code' => 'COMBINAISON', 'nom' => 'Salopette'],

            // ── VESTES ──
            ['code' => 'VESTE_CLASSIQUE', 'type_vetement_code' => 'VESTE', 'nom' => 'Veste classique'],
            ['code' => 'BLAZER', 'type_vetement_code' => 'VESTE', 'nom' => 'Blazer'],
            ['code' => 'VESTE_COSTUME', 'type_vetement_code' => 'VESTE', 'nom' => 'Veste de costume'],
            ['code' => 'VESTE_JEAN', 'type_vetement_code' => 'VESTE', 'nom' => 'Veste en jean'],
            // ['code' => 'VESTE_SAHARIENNE', 'type_vetement_code' => 'VESTE', 'nom' => 'Veste saharienne'],
            // ['code' => 'VESTE_MILITAIRE', 'type_vetement_code' => 'VESTE', 'nom' => 'Veste militaire'],

            // ── VESTES TRADITIONNELLES ──
            // ['code' => 'VESTE_WAX', 'type_vetement_code' => 'VESTE', 'nom' => 'Veste en wax'],
            ['code' => 'VESTE_AFRICAINE_BRODEE', 'type_vetement_code' => 'VESTE', 'nom' => 'Veste africaine brodée'],
            // ['code' => 'VESTE_BAZIN', 'type_vetement_code' => 'VESTE', 'nom' => 'Veste en bazin'],

            // ── TENUES TRADITIONNELLES ──
            ['code' => 'GRAND_BOUBOU', 'type_vetement_code' => 'TENUE_TRADITIONNELLE', 'nom' => 'Grand boubou'],
            ['code' => 'AGBADA', 'type_vetement_code' => 'TENUE_TRADITIONNELLE', 'nom' => 'Agbada'],
            ['code' => 'CAFTAN', 'type_vetement_code' => 'TENUE_TRADITIONNELLE', 'nom' => 'Caftan'],
            // ['code' => 'ENSEMBLE_AFRICAIN_HOMME', 'type_vetement_code' => 'TENUE_TRADITIONNELLE', 'nom' => 'Ensemble africain homme'],
            // ['code' => 'ENSEMBLE_AFRICAIN_FEMME', 'type_vetement_code' => 'TENUE_TRADITIONNELLE', 'nom' => 'Ensemble africain femme'],
            // ['code' => 'TENUE_YORUBA', 'type_vetement_code' => 'TENUE_TRADITIONNELLE', 'nom' => 'Tenue traditionnelle yoruba'],
            // ['code' => 'TENUE_FON', 'type_vetement_code' => 'TENUE_TRADITIONNELLE', 'nom' => 'Tenue traditionnelle fon'],
            // ['code' => 'TENUE_CEREMONIE_BAZIN', 'type_vetement_code' => 'TENUE_TRADITIONNELLE', 'nom' => 'Tenue de cérémonie en bazin'],
        ];

        foreach ($modeles as $modele) {
            $data = [
                'external_id' => (string) Str::uuid(),
                'type_vetement_id' => $typeId($modele['type_vetement_code']),
                'nom' => $modele['nom'],
                'description' => $modele['nom'],
                'portee' => 'public',
                'statut' => 'actif',
            ];

            if (isset($images[$modele['code']])) {
                $data['image_url'] = $images[$modele['code']];
            }

            ModeleVetement::create($data);
        }
    }
}
