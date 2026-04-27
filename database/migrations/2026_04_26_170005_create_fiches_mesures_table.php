<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiches_mesures', function (Blueprint $table) {
            $table->id();
            $table->char('external_id', 36)->unique('uniq_fiches_mesures_external_id');
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->date('date_prise');
            $table->enum('methode', ['PHOTO', 'MANUELLE']);
            $table->enum('statut_traitement', ['EN_ATTENTE', 'EN_COURS', 'TERMINE', 'ECHEC'])
                ->default('EN_ATTENTE');
            $table->char('traitement_id', 36)->nullable();
            $table->unsignedSmallInteger('version_regles')->nullable();
            $table->text('notes')->nullable();
            $table->enum('statut', ['BROUILLON', 'VALIDEE'])->default('BROUILLON');
            $table->timestamps();

            $table->index(['client_id', 'statut'], 'idx_fiches_mesures_client_statut');
            $table->index('statut_traitement', 'idx_fiches_mesures_statut_traitement');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiches_mesures');
    }
};
