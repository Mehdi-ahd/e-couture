<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modele_vetements', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->foreignId('prestataire_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('type_vetement_id')
                ->constrained('type_vetements')
                ->restrictOnDelete();
            $table->string('nom');
            $table->text('description');
            $table->string('portee');
            $table->string('statut');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modele_vetements');
    }
};
