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
            $table->string('statut')->default('en_attente'); // en_attente, en_coupe, en_cours, fini, livre
            $table->text('notes')->nullable();
            $table->date('date_commande');
            $table->date('date_livraison')->nullable();

            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->restrictOnDelete();

            $table->foreignId('model_vetement_id')
                  ->constrained('model_vetements')
                  ->restrictOnDelete();

            // Nullable : une commande peut référencer 0 ou 1 fiche de mesures (0..1)
            $table->foreignId('fiche_mesure_id')
                  ->nullable()
                  ->constrained('fiche_mesures')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commande_vetements');
    }
};
