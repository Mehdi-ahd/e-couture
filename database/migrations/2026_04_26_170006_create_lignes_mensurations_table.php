<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lignes_mensurations', function (Blueprint $table) {
            $table->id();
            $table->char('external_id', 36)->unique('uniq_lignes_mensurations_external_id');
            $table->foreignId('fiche_mesure_id')->constrained('fiches_mesures')->cascadeOnDelete();
            $table->foreignId('type_mensuration_id')->constrained('types_mensurations');
            $table->decimal('valeur', 7, 2);
            $table->enum('source', [
                'ESTIMEE',
                'MANUELLE',
                'DEDUITE_PROPORTION',
                'DEDUITE_REGLE_DE_TROIS',
                'DEDUITE_COMBINEE',
            ]);
            $table->decimal('confiance', 5, 4)->nullable();
            $table->text('commentaire')->nullable();
            $table->timestamps();

            $table->unique(
                ['fiche_mesure_id', 'type_mensuration_id'],
                'uniq_lignes_mensurations_fiche_type'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_mensurations');
    }
};
