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

        $modeles = [
            // ── T-SHIRTS ──
            ['code' => 'TSHIRT_CLASSIQUE', 'type_vetement_code' => 'TSHIRT', 'nom' => 'T-shirt classique'],
            ['code' => 'TSHIRT_COL_ROND', 'type_vetement_code' => 'TSHIRT', 'nom' => 'T-shirt col rond'],
            ['code' => 'TSHIRT_COL_V', 'type_vetement_code' => 'TSHIRT', 'nom' => 'T-shirt col V'],
            ['code' => 'TSHIRT_MANCHES_LONGUES', 'type_vetement_code' => 'TSHIRT', 'nom' => 'T-shirt manches longues'],
            ['code' => 'TSHIRT_MANCHES_COURTES', 'type_vetement_code' => 'TSHIRT', 'nom' => 'T-shirt manches courtes'],
            ['code' => 'TSHIRT_OVERSIZE', 'type_vetement_code' => 'TSHIRT', 'nom' => 'T-shirt oversize'],
            ['code' => 'POLO', 'type_vetement_code' => 'TSHIRT', 'nom' => 'Polo'],

            // ── CHEMISES ──
            ['code' => 'CHEMISE_CLASSIQUE', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise classique'],
            ['code' => 'CHEMISE_CINTREE', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise cintrée'],
            ['code' => 'CHEMISE_MANCHES_COURTES', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise manches courtes'],
            ['code' => 'CHEMISE_MANCHES_LONGUES', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise manches longues'],
            ['code' => 'CHEMISE_COL_MAO', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise col mao'],
            ['code' => 'CHEMISE_COL_OFFICIER', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise col officier'],
            ['code' => 'CHEMISE_HAWAIENNE', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise hawaïenne'],
            ['code' => 'CHEMISE_JEAN', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise en jean'],

            // ── CHEMISES TRADITIONNELLES ──
            ['code' => 'CHEMISE_WAX', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise en tissu wax'],
            ['code' => 'CHEMISE_AFRICAINE_BRODEE', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise africaine brodée'],
            ['code' => 'CHEMISE_BAZIN', 'type_vetement_code' => 'CHEMISE', 'nom' => 'Chemise en bazin'],

            // ── PULLS ──
            ['code' => 'PULL_COL_ROND', 'type_vetement_code' => 'PULL', 'nom' => 'Pull col rond'],
            ['code' => 'PULL_COL_V', 'type_vetement_code' => 'PULL', 'nom' => 'Pull col V'],
            ['code' => 'PULL_COL_ROULE', 'type_vetement_code' => 'PULL', 'nom' => 'Pull col roulé'],
            ['code' => 'SWEAT', 'type_vetement_code' => 'PULL', 'nom' => 'Sweat-shirt'],
            ['code' => 'HOODIE', 'type_vetement_code' => 'PULL', 'nom' => 'Hoodie (pull à capuche)'],
            ['code' => 'CARDIGAN', 'type_vetement_code' => 'PULL', 'nom' => 'Cardigan'],

            // ── DÉBARDEURS ──
            ['code' => 'DEBARDEUR_CLASSIQUE', 'type_vetement_code' => 'DEBARDEUR', 'nom' => 'Débardeur classique'],
            ['code' => 'DEBARDEUR_SPORT', 'type_vetement_code' => 'DEBARDEUR', 'nom' => 'Débardeur sport'],
            ['code' => 'MARCEL', 'type_vetement_code' => 'DEBARDEUR', 'nom' => 'Marcel'],

            // ── PANTALONS ──
            ['code' => 'PANTALON_DROIT', 'type_vetement_code' => 'PANTALON', 'nom' => 'Pantalon droit'],
            ['code' => 'PANTALON_SLIM', 'type_vetement_code' => 'PANTALON', 'nom' => 'Pantalon slim'],
            ['code' => 'PANTALON_REGULAR', 'type_vetement_code' => 'PANTALON', 'nom' => 'Pantalon regular'],
            ['code' => 'CHINO', 'type_vetement_code' => 'PANTALON', 'nom' => 'Chino'],
            ['code' => 'CARGO', 'type_vetement_code' => 'PANTALON', 'nom' => 'Cargo'],
            ['code' => 'PANTALON_PINCES', 'type_vetement_code' => 'PANTALON', 'nom' => 'Pantalon à pinces'],
            ['code' => 'PANTALON_LARGE', 'type_vetement_code' => 'PANTALON', 'nom' => 'Pantalon large'],
            ['code' => 'PANTALON_PALAZZO', 'type_vetement_code' => 'PANTALON', 'nom' => 'Pantalon palazzo'],
            ['code' => 'PANTALON_TAILLE_HAUTE', 'type_vetement_code' => 'PANTALON', 'nom' => 'Pantalon taille haute'],

            // ── PANTALONS TRADITIONNELS ──
            ['code' => 'PANTALON_AFRICAIN_DROIT', 'type_vetement_code' => 'PANTALON', 'nom' => 'Pantalon africain droit'],
            ['code' => 'PANTALON_BAZIN', 'type_vetement_code' => 'PANTALON', 'nom' => 'Pantalon en bazin'],
            ['code' => 'PANTALON_TRADITIONNEL_BRODE', 'type_vetement_code' => 'PANTALON', 'nom' => 'Pantalon traditionnel brodé'],

            // ── JEANS ──
            ['code' => 'JEAN_DROIT', 'type_vetement_code' => 'JEAN', 'nom' => 'Jean droit'],
            ['code' => 'JEAN_SLIM', 'type_vetement_code' => 'JEAN', 'nom' => 'Jean slim'],
            ['code' => 'JEAN_SKINNY', 'type_vetement_code' => 'JEAN', 'nom' => 'Jean skinny'],
            ['code' => 'JEAN_BOOTCUT', 'type_vetement_code' => 'JEAN', 'nom' => 'Jean bootcut'],
            ['code' => 'JEAN_FLARE', 'type_vetement_code' => 'JEAN', 'nom' => 'Jean flare'],
            ['code' => 'JEAN_BOYFRIEND', 'type_vetement_code' => 'JEAN', 'nom' => 'Jean boyfriend'],
            ['code' => 'JEAN_MOM', 'type_vetement_code' => 'JEAN', 'nom' => 'Jean mom'],
            ['code' => 'JEAN_CARGO', 'type_vetement_code' => 'JEAN', 'nom' => 'Jean cargo'],

            // ── JUPES ──
            ['code' => 'JUPE_DROITE', 'type_vetement_code' => 'JUPE', 'nom' => 'Jupe droite'],
            ['code' => 'JUPE_CRAYON', 'type_vetement_code' => 'JUPE', 'nom' => 'Jupe crayon'],
            ['code' => 'JUPE_TRAPEZE', 'type_vetement_code' => 'JUPE', 'nom' => 'Jupe trapèze'],
            ['code' => 'JUPE_EVASEE', 'type_vetement_code' => 'JUPE', 'nom' => 'Jupe évasée'],
            ['code' => 'JUPE_PLISSEE', 'type_vetement_code' => 'JUPE', 'nom' => 'Jupe plissée'],
            ['code' => 'JUPE_PORTEFEUILLE', 'type_vetement_code' => 'JUPE', 'nom' => 'Jupe portefeuille'],
            ['code' => 'JUPE_LONGUE', 'type_vetement_code' => 'JUPE', 'nom' => 'Jupe longue'],
            ['code' => 'JUPE_COURTE', 'type_vetement_code' => 'JUPE', 'nom' => 'Jupe courte'],

            // ── SHORTS ──
            ['code' => 'SHORT_CLASSIQUE', 'type_vetement_code' => 'SHORT', 'nom' => 'Short classique'],
            ['code' => 'SHORT_CARGO', 'type_vetement_code' => 'SHORT', 'nom' => 'Short cargo'],
            ['code' => 'BERMUDA', 'type_vetement_code' => 'SHORT', 'nom' => 'Bermuda'],
            ['code' => 'SHORT_JEAN', 'type_vetement_code' => 'SHORT', 'nom' => 'Short en jean'],
            ['code' => 'SHORT_SPORT', 'type_vetement_code' => 'SHORT', 'nom' => 'Short de sport'],

            // ── ROBES ──
            ['code' => 'ROBE_DROITE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe droite'],
            ['code' => 'ROBE_FOURREAU', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe fourreau'],
            ['code' => 'ROBE_PORTEFEUILLE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe portefeuille'],
            ['code' => 'ROBE_TRAPEZE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe trapèze'],
            ['code' => 'ROBE_PATINEUSE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe patineuse'],
            ['code' => 'ROBE_CHEMISE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe chemise'],
            ['code' => 'ROBE_LONGUE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe longue'],
            ['code' => 'ROBE_COURTE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe courte'],
            ['code' => 'ROBE_BUSTIER', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe bustier'],
            ['code' => 'ROBE_EMPIRE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe empire'],
            ['code' => 'ROBE_SIRENE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe sirène'],
            ['code' => 'ROBE_PRINCESSE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe princesse'],
            ['code' => 'ROBE_ASYMETRIQUE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe asymétrique'],
            ['code' => 'ROBE_DOS_NU', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe dos nu'],

            // ── ROBES TRADITIONNELLES ──
            ['code' => 'ROBE_WAX', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe en wax'],
            ['code' => 'ROBE_KABA', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe kaba'],
            ['code' => 'ROBE_AFRICAINE_LONGUE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe africaine longue'],
            ['code' => 'ROBE_AFRICAINE_CINTREE', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe africaine cintrée'],
            ['code' => 'ROBE_CEREMONIE_BAZIN', 'type_vetement_code' => 'ROBE', 'nom' => 'Robe de cérémonie en bazin'],

            // ── COMBINAISONS ──
            ['code' => 'COMBI_PANTALON', 'type_vetement_code' => 'COMBINAISON', 'nom' => 'Combinaison pantalon'],
            ['code' => 'COMBI_SHORT', 'type_vetement_code' => 'COMBINAISON', 'nom' => 'Combinaison short'],
            ['code' => 'COMBI_MANCHES', 'type_vetement_code' => 'COMBINAISON', 'nom' => 'Combinaison à manches'],
            ['code' => 'COMBI_SANS_MANCHES', 'type_vetement_code' => 'COMBINAISON', 'nom' => 'Combinaison sans manches'],
            ['code' => 'SALOPETTE', 'type_vetement_code' => 'COMBINAISON', 'nom' => 'Salopette'],

            // ── MANTEAUX ──
            ['code' => 'MANTEAU_CLASSIQUE', 'type_vetement_code' => 'MANTEAU', 'nom' => 'Manteau classique'],
            ['code' => 'TRENCH_COAT', 'type_vetement_code' => 'MANTEAU', 'nom' => 'Trench-coat'],
            ['code' => 'PARDESSUS', 'type_vetement_code' => 'MANTEAU', 'nom' => 'Pardessus'],
            ['code' => 'CABAN', 'type_vetement_code' => 'MANTEAU', 'nom' => 'Caban'],
            ['code' => 'PARKA', 'type_vetement_code' => 'MANTEAU', 'nom' => 'Parka'],

            // ── VESTES ──
            ['code' => 'VESTE_CLASSIQUE', 'type_vetement_code' => 'VESTE', 'nom' => 'Veste classique'],
            ['code' => 'BLAZER', 'type_vetement_code' => 'VESTE', 'nom' => 'Blazer'],
            ['code' => 'VESTE_COSTUME', 'type_vetement_code' => 'VESTE', 'nom' => 'Veste de costume'],
            ['code' => 'VESTE_JEAN', 'type_vetement_code' => 'VESTE', 'nom' => 'Veste en jean'],
            ['code' => 'VESTE_SAHARIENNE', 'type_vetement_code' => 'VESTE', 'nom' => 'Veste saharienne'],
            ['code' => 'VESTE_MILITAIRE', 'type_vetement_code' => 'VESTE', 'nom' => 'Veste militaire'],

            // ── VESTES TRADITIONNELLES ──
            ['code' => 'VESTE_WAX', 'type_vetement_code' => 'VESTE', 'nom' => 'Veste en wax'],
            ['code' => 'VESTE_AFRICAINE_BRODEE', 'type_vetement_code' => 'VESTE', 'nom' => 'Veste africaine brodée'],
            ['code' => 'VESTE_BAZIN', 'type_vetement_code' => 'VESTE', 'nom' => 'Veste en bazin'],

            // ── BLOUSONS ──
            ['code' => 'BLOUSON_BOMBER', 'type_vetement_code' => 'BLOUSON', 'nom' => 'Blouson bomber'],
            ['code' => 'BLOUSON_AVIATEUR', 'type_vetement_code' => 'BLOUSON', 'nom' => 'Blouson aviateur'],
            ['code' => 'BLOUSON_CUIR', 'type_vetement_code' => 'BLOUSON', 'nom' => 'Blouson en cuir'],
            ['code' => 'BLOUSON_COUPE_VENT', 'type_vetement_code' => 'BLOUSON', 'nom' => 'Blouson coupe-vent'],

            // ── DOUDOUNES ──
            ['code' => 'DOUDOUNE_LEGERE', 'type_vetement_code' => 'DOUDOUNE', 'nom' => 'Doudoune légère'],
            ['code' => 'DOUDOUNE_LONGUE', 'type_vetement_code' => 'DOUDOUNE', 'nom' => 'Doudoune longue'],
            ['code' => 'DOUDOUNE_SANS_MANCHES', 'type_vetement_code' => 'DOUDOUNE', 'nom' => 'Doudoune sans manches'],

            // ── CHAUSSETTES ──
            ['code' => 'CHAUSSETTE_COURTE', 'type_vetement_code' => 'CHAUSSETTE', 'nom' => 'Chaussettes courtes'],
            ['code' => 'CHAUSSETTE_MI_HAUTE', 'type_vetement_code' => 'CHAUSSETTE', 'nom' => 'Chaussettes mi-hautes'],
            ['code' => 'CHAUSSETTE_LONGUE', 'type_vetement_code' => 'CHAUSSETTE', 'nom' => 'Chaussettes longues'],
            ['code' => 'SOCQUETTE', 'type_vetement_code' => 'CHAUSSETTE', 'nom' => 'Socquettes'],

            // ── SOUS-VÊTEMENTS HOMME ──
            ['code' => 'SLIP', 'type_vetement_code' => 'SOUS_VETEMENT', 'nom' => 'Slip'],
            ['code' => 'BOXER', 'type_vetement_code' => 'SOUS_VETEMENT', 'nom' => 'Boxer'],
            ['code' => 'CALECON', 'type_vetement_code' => 'SOUS_VETEMENT', 'nom' => 'Caleçon'],
            ['code' => 'DEBARDEUR_CORPS', 'type_vetement_code' => 'SOUS_VETEMENT', 'nom' => 'Débardeur de corps'],

            // ── SOUS-VÊTEMENTS FEMME ──
            ['code' => 'CULOTTE_CLASSIQUE', 'type_vetement_code' => 'SOUS_VETEMENT', 'nom' => 'Culotte classique'],
            ['code' => 'SHORTY', 'type_vetement_code' => 'SOUS_VETEMENT', 'nom' => 'Shorty'],
            ['code' => 'STRING', 'type_vetement_code' => 'SOUS_VETEMENT', 'nom' => 'String'],
            ['code' => 'TANGA', 'type_vetement_code' => 'SOUS_VETEMENT', 'nom' => 'Tanga'],
            ['code' => 'BRASSIERE', 'type_vetement_code' => 'SOUS_VETEMENT', 'nom' => 'Brassière'],
            ['code' => 'SOUTIEN_GORGE', 'type_vetement_code' => 'SOUS_VETEMENT', 'nom' => 'Soutien-gorge'],

            // ── ÉCHARPES ──
            ['code' => 'ECHARPE_CLASSIQUE', 'type_vetement_code' => 'ECHARPE', 'nom' => 'Écharpe classique'],
            ['code' => 'CHECHE', 'type_vetement_code' => 'ECHARPE', 'nom' => 'Chèche'],
            ['code' => 'FOULARD', 'type_vetement_code' => 'ECHARPE', 'nom' => 'Foulard'],

            // ── BONNETS ──
            ['code' => 'BONNET_CLASSIQUE', 'type_vetement_code' => 'BONNET', 'nom' => 'Bonnet classique'],
            ['code' => 'BONNET_REVERS', 'type_vetement_code' => 'BONNET', 'nom' => 'Bonnet à revers'],
            ['code' => 'BERET', 'type_vetement_code' => 'BONNET', 'nom' => 'Béret'],
            ['code' => 'BONNET_POMPON', 'type_vetement_code' => 'BONNET', 'nom' => 'Bonnet à pompon'],

            // ── TENUES TRADITIONNELLES ──
            ['code' => 'GRAND_BOUBOU', 'type_vetement_code' => 'TENUE_TRADITIONNELLE', 'nom' => 'Grand boubou'],
            ['code' => 'AGBADA', 'type_vetement_code' => 'TENUE_TRADITIONNELLE', 'nom' => 'Agbada'],
            ['code' => 'CAFTAN', 'type_vetement_code' => 'TENUE_TRADITIONNELLE', 'nom' => 'Caftan'],
            ['code' => 'ENSEMBLE_AFRICAIN_HOMME', 'type_vetement_code' => 'TENUE_TRADITIONNELLE', 'nom' => 'Ensemble africain homme'],
            ['code' => 'ENSEMBLE_AFRICAIN_FEMME', 'type_vetement_code' => 'TENUE_TRADITIONNELLE', 'nom' => 'Ensemble africain femme'],
            ['code' => 'TENUE_YORUBA', 'type_vetement_code' => 'TENUE_TRADITIONNELLE', 'nom' => 'Tenue traditionnelle yoruba'],
            ['code' => 'TENUE_FON', 'type_vetement_code' => 'TENUE_TRADITIONNELLE', 'nom' => 'Tenue traditionnelle fon'],
            ['code' => 'TENUE_CEREMONIE_BAZIN', 'type_vetement_code' => 'TENUE_TRADITIONNELLE', 'nom' => 'Tenue de cérémonie en bazin'],
        ];

        foreach ($modeles as $modele) {
            ModeleVetement::create([
                'external_id' => (string) Str::uuid(),
                'type_vetement_id' => $typeId($modele['type_vetement_code']),
                'nom' => $modele['nom'],
                'description' => $modele['nom'],
                'portee' => 'public',
                'statut' => 'actif',
            ]);
        }
    }
}
