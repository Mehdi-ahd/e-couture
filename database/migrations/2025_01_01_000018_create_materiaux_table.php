<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catalogue global de matériaux (tissus, doublures, entoilages, accessoires…).
     * Un matériau peut être visuellement associé à une forme de découpe de référence.
     */
    public function up(): void
    {
        Schema::create('materiaux', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();

            $table->string('nom');
            $table->text('description')->nullable();

            // Catégorie du matériau : tissu, doublure, entoilage, accessoire, etc.
            $table->string('type');

            $table->string('image_url')->nullable();

            // true = visible par tous les prestataires, false = usage interne
            $table->boolean('est_global')->default(true);

            // Forme de découpe associée pour la visualisation (0..1)
            $table->foreignId('forme_decoupe_id')
                  ->nullable()
                  ->constrained('formes_decoupe')
                  ->nullOnDelete();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materiaux');
    }
};
