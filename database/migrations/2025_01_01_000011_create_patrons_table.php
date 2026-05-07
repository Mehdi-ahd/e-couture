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
            $table->string('methode'); // manuel, genere
            $table->integer('version')->default(1);
            $table->string('fichier_url')->nullable();
            $table->jsonb('donnees_dessin')->nullable();
            $table->string('statut')->default('brouillon'); // brouillon, valide, archive
            $table->foreignId('model_vetement_id')
                  ->constrained('model_vetements')
                  ->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrons');
    }
};
