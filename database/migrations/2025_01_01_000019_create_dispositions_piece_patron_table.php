<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Placement d'une forme de découpe sur une pièce de patron.
     * Chaque disposition associe une FormeDecoupe à une PiecePatron avec
     * sa position, rotation et échelle dans le repère 2D de la pièce.
     */
    public function up(): void
    {
        Schema::create('dispositions_piece_patron', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();

            // Coordonnées du point d'ancrage de la forme dans le repère de la pièce (unité : mm)
            $table->double('position_x');
            $table->double('position_y');

            // Rotation en degrés (0.0 à 360.0)
            $table->double('rotation')->default(0.0);

            // Facteur d'échelle (1.0 = taille réelle)
            $table->double('echelle')->default(1.0);

            // Ordre d'affichage / superposition (z-index)
            $table->integer('ordre')->default(0);

            // Pièce de patron sur laquelle la forme est posée (1..*)
            $table->foreignId('piece_patron_id')
                  ->constrained('pieces_patron')
                  ->cascadeOnDelete();

            // Forme de découpe appliquée (obligatoire)
            $table->foreignId('forme_decoupe_id')
                  ->constrained('formes_decoupe')
                  ->restrictOnDelete();

            // Matériau associé à cette disposition (optionnel, 0..1)
            $table->foreignId('materiau_id')
                  ->nullable()
                  ->constrained('materiaux')
                  ->nullOnDelete();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispositions_piece_patron');
    }
};
