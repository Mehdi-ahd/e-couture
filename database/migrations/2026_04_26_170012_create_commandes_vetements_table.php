<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commandes_vetements', function (Blueprint $table) {
            $table->id();
            $table->char('external_id', 36)->unique('uniq_commandes_vetements_external_id');
            $table->foreignId('fiche_client_id')->constrained('fiches_clients');
            $table->foreignId('modele_vetement_id')->constrained('modeles_vetements');
            $table->foreignId('fiche_mesure_id')->constrained('fiches_mesures');
            $table->enum('statut', ['EN_ATTENTE', 'EN_CONFECTION', 'TERMINE', 'ANNULE'])
                ->default('EN_ATTENTE');
            $table->text('notes')->nullable();
            $table->timestamp('date_commande')->useCurrent();
            $table->date('date_livraison')->nullable();
            $table->timestamps();

            $table->index(['fiche_client_id', 'statut'], 'idx_commandes_vetements_fiche_statut');
            $table->index('modele_vetement_id', 'idx_commandes_vetements_modele');
            $table->index('date_commande', 'idx_commandes_vetements_date_commande');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandes_vetements');
    }
};
