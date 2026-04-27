<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->char('external_id', 36)->unique('uniq_paiements_external_id');
            $table->foreignId('commande_id')->constrained('commandes_vetements');
            $table->enum('mode', [
                'KKIAPAY_MOMO',
                'KKIAPAY_CARTE',
                'FEDAPAY_MOMO',
                'FEDAPAY_CARTE',
                'ESPECES',
            ]);
            $table->decimal('montant', 12, 2);
            $table->char('devise', 3)->default('XOF');
            $table->enum('statut', ['INITIE', 'EN_ATTENTE', 'REUSSI', 'ECHEC', 'REMBOURSE'])
                ->default('INITIE');
            $table->timestamp('date_initiation')->useCurrent();
            $table->timestamp('date_confirmation')->nullable();
            $table->string('reference_externe', 100)->nullable();
            $table->json('metadonnees_agregateur')->nullable();
            $table->timestamps();

            $table->unique('reference_externe', 'uniq_paiements_reference_externe');
            $table->index(['commande_id', 'statut'], 'idx_paiements_commande_statut');
            $table->index('reference_externe', 'idx_paiements_reference_externe');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
