<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('piece_patrons', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->foreignUuid('patron_id')
                  ->constrained('patrons')
                  ->cascadeOnDelete();
            $table->string('nom');
            $table->unsignedInteger('ordre');
            $table->json('donnees_geometriques');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piece_patrons');
    }
};
