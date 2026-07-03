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
            $table->uuid('external_id')->unique();
            $table->foreignId('modele_vetement_id')
                ->nullable()
                ->unique()
                ->constrained('modele_vetements')
                ->nullOnDelete();
            $table->string('methode');
            $table->unsignedInteger('version')->default(1);
            $table->string('fichier_url')->nullable();
            $table->json('donnees_dessin')->nullable();
            $table->string('statut');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrons');
    }
};
