<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pieces_patrons', function (Blueprint $table) {
            $table->id();
            $table->char('external_id', 36)->unique('uniq_pieces_patrons_external_id');
            $table->foreignId('patron_id')->constrained('patrons')->cascadeOnDelete();
            $table->string('nom', 100);
            $table->unsignedSmallInteger('ordre')->default(1);
            $table->json('donnees_geometriques');
            $table->timestamps();

            $table->index(['patron_id', 'ordre'], 'idx_pieces_patrons_patron_ordre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pieces_patrons');
    }
};
