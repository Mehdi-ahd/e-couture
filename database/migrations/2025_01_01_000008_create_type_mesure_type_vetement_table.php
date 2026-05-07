<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table pivot représentant la relation "TypeMesure pivot TypeVetement".
     * Un TypeMesure est le pivot de zéro ou plusieurs TypeVetement.
     */
    public function up(): void
    {
        Schema::create('type_mesure_type_vetement', function (Blueprint $table) {
            $table->foreignId('type_mesure_id')
                  ->constrained('type_mesures')
                  ->cascadeOnDelete();
            $table->foreignId('type_vetement_id')
                  ->constrained('type_vetements')
                  ->cascadeOnDelete();
            $table->primary(['type_mesure_id', 'type_vetement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('type_mesure_type_vetement');
    }
};
