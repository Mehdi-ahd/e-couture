<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrons', function (Blueprint $table) {
            $table->id();
            $table->char('external_id', 36)->unique('uniq_patrons_external_id');
            $table->foreignId('modele_vetement_id')->unique('uniq_patrons_modele_vetement_id')
                ->constrained('modeles_vetements')
                ->cascadeOnDelete();
            $table->enum('methode', ['UPLOAD', 'CREATION', 'IA']);
            $table->unsignedSmallInteger('version')->default(1);
            $table->string('fichier_url', 500)->nullable();
            $table->json('donnees_dessin')->nullable();
            $table->enum('statut', ['BROUILLON', 'VALIDE'])->default('BROUILLON');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrons');
    }
};
