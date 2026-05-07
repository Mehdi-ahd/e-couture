<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesure_modeles', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->decimal('valeur', 8, 2);
            $table->text('notes')->nullable();
            $table->foreignId('model_vetement_id')
                  ->constrained('model_vetements')
                  ->cascadeOnDelete();
            $table->foreignId('type_mesure_id')
                  ->constrained('type_mesures')
                  ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesure_modeles');
    }
};
