<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiche_mesures', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->date('date');
            $table->string('methode'); // manuelle, ia_assistee
            $table->string('statut_traitement')->default('en_attente'); // en_attente, traite, echec
            $table->string('traitement_id')->nullable();
            $table->integer('version_regles')->default(1);
            $table->text('notes')->nullable();
            $table->string('statut')->default('brouillon'); // brouillon, valide, archive
            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->cascadeOnDelete();
            $table->foreignId('prestataire_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiche_mesures');
    }
};
