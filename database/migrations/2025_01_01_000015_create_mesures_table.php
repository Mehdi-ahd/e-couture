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
            $table->foreignUuid('fiche_mesure_id')
                  ->constrained('fiche_mesures')
                  ->cascadeOnDelete();
            $table->foreignUuid('type_mesure_id')
                  ->constrained('type_mesures')
                  ->restrictOnDelete();
            $table->decimal('valeur', 8, 2);
            $table->string('source');
            $table->decimal('confiance', 5, 4)->nullable();
            $table->text('commentaire')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesures');
    }
};
