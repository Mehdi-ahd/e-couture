<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modele_vetement_materiau', function (Blueprint $table) {
            $table->foreignId('modele_vetement_id')
                ->constrained('modele_vetements')
                ->cascadeOnDelete();
            $table->foreignId('materiau_id')
                ->constrained('materiaux')
                ->cascadeOnDelete();
            $table->primary(['modele_vetement_id', 'materiau_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modele_vetement_materiau');
    }
};
