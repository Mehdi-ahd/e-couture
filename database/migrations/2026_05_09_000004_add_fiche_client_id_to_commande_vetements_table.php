<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commande_vetements', function (Blueprint $table) {
            if (! Schema::hasColumn('commande_vetements', 'fiche_client_id')) {
                $table->foreignId('fiche_client_id')
                    ->nullable()
                    ->after('client_id')
                    ->constrained('fiches_clients')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('commande_vetements', function (Blueprint $table) {
            if (Schema::hasColumn('commande_vetements', 'fiche_client_id')) {
                $table->dropConstrainedForeignId('fiche_client_id');
            }
        });
    }
};
