<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesures', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->decimal('valeur', 8, 2);
            $table->string('source'); // ia, manuelle, corrigee
            $table->decimal('confiance', 5, 4)->nullable(); // score IA entre 0.0000 et 1.0000
            $table->text('commentaire')->nullable();
            $table->foreignId('fiche_mesure_id')
                  ->constrained('fiche_mesures')
                  ->cascadeOnDelete();
            $table->foreignId('type_mesure_id')
                  ->constrained('type_mesures')
                  ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesures');
    }
};
