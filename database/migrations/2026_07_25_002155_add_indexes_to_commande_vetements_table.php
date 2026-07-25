<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('commande_vetements', function (Blueprint $table) {
            $table->index('client_id');
            $table->index('modele_vetement_id');
            $table->index('fiche_mesure_id');
        });
    }

    public function down(): void
    {
        Schema::table('commande_vetements', function (Blueprint $table) {
            $table->dropIndex(['client_id']);
            $table->dropIndex(['modele_vetement_id']);
            $table->dropIndex(['fiche_mesure_id']);
        });
    }
};
