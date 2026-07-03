<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commande_vetements', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->foreignId('client_id')
                ->constrained('clients')
                ->restrictOnDelete();
            $table->foreignId('modele_vetement_id')
                ->constrained('modele_vetements')
                ->restrictOnDelete();
            $table->foreignId('fiche_mesure_id')
                ->nullable()
                ->constrained('fiche_mesures')
                ->nullOnDelete();
            $table->string('statut');
            $table->text('notes')->nullable();
            $table->date('date_commande');
            $table->date('date_livraison')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commande_vetements');
    }
};
