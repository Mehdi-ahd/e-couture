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
            $table->char('external_id', 36)->unique('uniq_regles_proportions_external_id');
            $table->string('nom', 150);
            $table->foreignId('mensuration_source_id')->constrained('types_mensurations');
            $table->foreignId('mensuration_cible_id')->constrained('types_mensurations');
            $table->decimal('coefficient', 8, 4);
            $table->decimal('offset', 8, 2)->default(0);
            $table->string('source_metier', 100)->nullable();
            $table->unsignedSmallInteger('version')->default(1);
            $table->boolean('est_active')->default(true);
            $table->timestamps();

            $table->unique(
                ['mensuration_source_id', 'mensuration_cible_id', 'version'],
                'uniq_regles_proportions_source_cible_version'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regles_proportions');
    }
};
