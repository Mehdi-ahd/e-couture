<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bibliothèque globale de formes de découpe (partagée par tous les prestataires).
     * Stocke les données géométriques vectorielles de chaque forme.
     */
    public function up(): void
    {
        Schema::create('formes_decoupe', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();

            $table->string('nom');
            $table->text('description')->nullable();

            // Données vectorielles / SVG / JSON décrivant la forme géométrique
            $table->jsonb('donnees_formes');

            $table->string('miniature_url')->nullable();

            // Origine de la forme : system, import, custom
            $table->string('source');

            // true = visible par tous les prestataires, false = usage interne
            $table->boolean('est_global')->default(true);

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formes_decoupe');
    }
};
