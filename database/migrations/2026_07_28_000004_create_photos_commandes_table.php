<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photos_commandes', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->foreignId('commande_id')->constrained('commande_vetements')->cascadeOnDelete();
            $table->string('url');
            $table->text('description')->nullable();
            $table->string('categorie'); // general, modele, tissu, detail, avant, apres
            $table->dateTime('date_prise');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos_commandes');
    }
};
