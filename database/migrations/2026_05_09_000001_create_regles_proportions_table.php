<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regles_proportions', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->string('nom');
            $table->foreignId('type_mesure_source_id')
                ->constrained('type_mesures')
                ->restrictOnDelete();
            $table->foreignId('type_mesure_cible_id')
                ->constrained('type_mesures')
                ->restrictOnDelete();
            $table->decimal('coefficient', 8, 4);
            $table->decimal('offset', 8, 2)->default(0);
            $table->string('source_metier')->nullable();
            $table->unsignedSmallInteger('version')->default(1);
            $table->boolean('est_active')->default(true);
            $table->timestamps();

            $table->unique(
                ['type_mesure_source_id', 'type_mesure_cible_id', 'version'],
                'uniq_regles_proportions_mesures_version',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regles_proportions');
    }
};
