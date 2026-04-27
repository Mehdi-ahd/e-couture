<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('types_vetements', function (Blueprint $table) {
            $table->id();
            $table->char('external_id', 36)->unique('uniq_types_vetements_external_id');
            $table->string('code', 50);
            $table->string('nom', 120);
            $table->enum('categorie', ['HAUT', 'BAS', 'ROBE', 'ENSEMBLE', 'ACCESSOIRE']);
            $table->foreignId('mensuration_pivot_id')->constrained('types_mensurations');
            $table->text('description')->nullable();
            $table->boolean('est_actif')->default(true);
            $table->timestamps();

            $table->unique('code', 'uniq_types_vetements_code');
            $table->index('categorie', 'idx_types_vetements_categorie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('types_vetements');
    }
};
