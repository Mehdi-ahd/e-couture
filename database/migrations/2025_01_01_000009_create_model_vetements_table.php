<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_vetements', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->string('nom');
            $table->text('description');
            $table->string('portee'); // prive, public, bibliotheque
            $table->string('statut')->default('brouillon'); // brouillon, publie, archive
            $table->foreignId('prestataire_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignId('type_vetement_id')
                  ->constrained('type_vetements')
                  ->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_vetements');
    }
};
