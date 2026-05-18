<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesure_modeles', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->foreignId('modele_vetement_id')
                  ->constrained('modele_vetements')
                  ->cascadeOnDelete();
            $table->foreignId('type_mesure_id')
                  ->constrained('type_mesures')
                  ->restrictOnDelete();
            $table->decimal('valeur', 8, 2);
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesure_modeles');
    }
};
