<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $tables = [
        'users',
        'type_vetements',
        'clients',
        'model_vetements',
        'type_mesures',
        'mesure_modeles',
        'patrons',
        'pieces_patron',
        'annotation_patrons',
        'fiche_mesures',
        'mesures',
        'commande_vetements',
        'formes_decoupe',
        'materiaux',
        'dispositions_piece_patron',
        'social_accounts',
        'regles_proportions',
        'paiements',
        'notes_couturiers',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE EXTENSION IF NOT EXISTS pgcrypto');

        foreach ($this->tables as $table) {
            DB::statement(sprintf(
                'ALTER TABLE "%s" ALTER COLUMN "external_id" SET DEFAULT gen_random_uuid()',
                $table,
            ));
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->tables as $table) {
            DB::statement(sprintf(
                'ALTER TABLE "%s" ALTER COLUMN "external_id" DROP DEFAULT',
                $table,
            ));
        }
    }
};
