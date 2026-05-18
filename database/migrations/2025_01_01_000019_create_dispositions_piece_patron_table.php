<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disposition_piece_patrons', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->foreignId('piece_patron_id')
                  ->constrained('piece_patrons')
                  ->cascadeOnDelete();
            $table->foreignId('forme_decoupe_id')
                  ->constrained('formes_decoupe')
                  ->restrictOnDelete();
            $table->foreignId('materiau_id')
                  ->nullable()
                  ->constrained('materiaux')
                  ->nullOnDelete();
            $table->decimal('position_x', 10, 4);
            $table->decimal('position_y', 10, 4);
            $table->decimal('rotation', 8, 4);
            $table->decimal('echelle', 8, 4);
            $table->unsignedInteger('ordre');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disposition_piece_patrons');
    }
};
