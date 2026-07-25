<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    // -------------------------------------------------------------------------
    // 4 codes à renommer (code + nom + description mis à jour)
    // -------------------------------------------------------------------------
    private array $renommages = [
        'BRA_TOTAL'    => ['code' => 'MANCHE_LONGUE',  'nom' => 'Longueur manche longue', 'description' => 'Longueur de l\'épaule au poignet.'],
        'BRA_HAUT'     => ['code' => 'MANCHE_COURTE',  'nom' => 'Longueur manche courte', 'description' => 'Longueur de l\'épaule au coude.'],
        'TAILLE'       => ['code' => 'CEINTURE',        'nom' => 'Tour de ceinture',       'description' => 'Tour de ceinture estimé par ellipse ou ratio.'],
        'TOUR_HANCHES' => ['code' => 'TOUR_FESSES',     'nom' => 'Tour de fesses',         'description' => 'Tour de fesses estimé par ellipse ou ratio.'],
    ];

    // -------------------------------------------------------------------------
    // 9 nouveaux codes à insérer
    // -------------------------------------------------------------------------
    private array $ajouts = [
        ['code' => 'HAUT_SEIN',        'nom' => 'Hauteur de sein',         'unite' => 'cm', 'categorie' => 'longueur',      'description' => 'Distance épaule → niveau buste (ratio torse).'],
        ['code' => 'LONGUEUR_TAILLE',  'nom' => 'Longueur taille',         'unite' => 'cm', 'categorie' => 'longueur',      'description' => 'Distance épaule → nombril.'],
        ['code' => 'LONGUEUR_CHEMISE', 'nom' => 'Longueur chemise',        'unite' => 'cm', 'categorie' => 'longueur',      'description' => 'Distance nombril → hanche.'],
        ['code' => 'LONGUEUR_JUPE',    'nom' => 'Longueur jupe longue',    'unite' => 'cm', 'categorie' => 'longueur',      'description' => 'Distance hanche → cheville.'],
        ['code' => 'LONGUEUR_ROBE',    'nom' => 'Longueur robe',           'unite' => 'cm', 'categorie' => 'longueur',      'description' => 'Distance épaule → cheville.'],
        ['code' => 'HAUTEUR_GENOU',    'nom' => 'Hauteur genou',           'unite' => 'cm', 'categorie' => 'longueur',      'description' => 'Distance sol → genou (talon + bonus 6 cm).'],
        ['code' => 'CARRURE_DEVANT',   'nom' => 'Carrure devant',          'unite' => 'cm', 'categorie' => 'largeur',       'description' => 'Largeur entre les emmanchures côté face.'],
        ['code' => 'CARRURE_DOS',      'nom' => 'Carrure dos',             'unite' => 'cm', 'categorie' => 'largeur',       'description' => 'Largeur entre les emmanchures côté dos.'],
        ['code' => 'TOUR_BAS',         'nom' => 'Tour du bas (cheville)',  'unite' => 'cm', 'categorie' => 'circonference', 'description' => 'Périmètre de la cheville estimé par ratio.'],
    ];

    /**
     * Applique la synchronisation : renommages + insertions.
     */
    public function up(): void
    {
        // ── 1. Renommages ────────────────────────────────────────────────
        foreach ($this->renommages as $ancien => $nouveau) {
            // Mettre à jour le code dans type_mesures
            DB::table('type_mesures')
                ->where('code', $ancien)
                ->update([
                    'code'        => $nouveau['code'],
                    'nom'         => $nouveau['nom'],
                    'description' => $nouveau['description'],
                ]);

            // Répercuter sur la table mesures (clé étrangère)
        }

        // ── 2. Insertions des nouveaux types ─────────────────────────────
        foreach ($this->ajouts as $type) {
            // Éviter les doublons si la migration tourne deux fois
            $existe = DB::table('type_mesures')
                ->where('code', $type['code'])
                ->exists();

            if (! $existe) {
                DB::table('type_mesures')->insert([
                    'external_id' => (string) Str::uuid(),
                    'code'        => $type['code'],
                    'nom'         => $type['nom'],
                    'unite'       => $type['unite'],
                    'categorie'   => $type['categorie'],
                    'description' => $type['description'],
                    'est_actif'   => true,
                ]);
            }
        }
    }

    /**
     * Annule la migration (rollback complet).
     */
    public function down(): void
    {
        // Supprimer les types ajoutés
        foreach ($this->ajouts as $type) {
            DB::table('type_mesures')->where('code', $type['code'])->delete();
        }

        // Remettre les anciens codes
        foreach ($this->renommages as $ancien => $nouveau) {
            DB::table('type_mesures')
                ->where('code', $nouveau['code'])
                ->update(['code' => $ancien]);

        }
    }
};
