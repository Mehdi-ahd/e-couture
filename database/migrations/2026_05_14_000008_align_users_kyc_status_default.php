<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(<<<'SQL'
            UPDATE "users"
            SET "kyc_statut" = 'NON_SOUMIS'
            WHERE "kyc_statut" = 'en_attente'
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE "users"
            ALTER COLUMN "kyc_statut" SET DEFAULT 'NON_SOUMIS'
        SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(<<<'SQL'
            UPDATE "users"
            SET "kyc_statut" = 'en_attente'
            WHERE "kyc_statut" = 'NON_SOUMIS'
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE "users"
            ALTER COLUMN "kyc_statut" SET DEFAULT 'en_attente'
        SQL);
    }
};
