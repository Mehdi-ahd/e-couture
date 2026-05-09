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
            $table->uuid('external_id')->unique();
            $table->foreignId('commande_id')
                ->constrained('commande_vetements')
                ->cascadeOnDelete();
            $table->string('mode', 40);
            $table->decimal('montant', 12, 2);
            $table->char('devise', 3)->default('XOF');
            $table->string('statut', 40)->default('INITIE');
            $table->timestamp('date_initiation')->useCurrent();
            $table->timestamp('date_confirmation')->nullable();
            $table->string('reference_externe', 100)->nullable()->unique();
            $table->json('metadonnees_agregateur')->nullable();
            $table->timestamps();

            $table->index(['commande_id', 'statut'], 'idx_paiements_commande_statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
