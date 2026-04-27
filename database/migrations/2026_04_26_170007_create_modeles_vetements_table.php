<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modeles_vetements', function (Blueprint $table) {
            $table->id();
            $table->char('external_id', 36)->unique('uniq_modeles_vetements_external_id');
            $table->foreignId('type_vetement_id')->constrained('types_vetements');
            $table->foreignId('createur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nom', 150);
            $table->text('description')->nullable();
            $table->enum('portee', ['PRIVE', 'GLOBAL']);
            $table->enum('statut', ['BROUILLON', 'ACTIF', 'SOUMIS', 'REJETE', 'ARCHIVE'])
                ->default('BROUILLON');
            $table->timestamps();

            $table->index(['portee', 'statut'], 'idx_modeles_vetements_portee_statut');
            $table->index('createur_id', 'idx_modeles_vetements_createur');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modeles_vetements');
    }
};
