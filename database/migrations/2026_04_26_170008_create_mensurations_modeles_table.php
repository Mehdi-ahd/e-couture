<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensurations_modeles', function (Blueprint $table) {
            $table->id();
            $table->char('external_id', 36)->unique('uniq_mensurations_modeles_external_id');
            $table->foreignId('modele_vetement_id')->constrained('modeles_vetements')->cascadeOnDelete();
            $table->foreignId('type_mensuration_id')->constrained('types_mensurations');
            $table->decimal('valeur', 7, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(
                ['modele_vetement_id', 'type_mensuration_id'],
                'uniq_mensurations_modeles_modele_type'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensurations_modeles');
    }
};
