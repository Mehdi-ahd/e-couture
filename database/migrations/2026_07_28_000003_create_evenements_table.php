<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evenements', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->foreignId('commande_id')->constrained('commande_vetements')->cascadeOnDelete();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->dateTime('date');
            $table->string('type'); // essayage, livraison, rendez_vous, prise_mesures, autre
            $table->boolean('est_complete')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evenements');
    }
};
