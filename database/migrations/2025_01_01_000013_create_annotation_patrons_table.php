<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annotation_patrons', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->foreignId('piece_patron_id')
                ->constrained('piece_patrons')
                ->cascadeOnDelete();
            $table->foreignId('type_mesure_id')
                ->constrained('type_mesures')
                ->restrictOnDelete();
            $table->string('label');
            $table->string('position_depart');
            $table->string('position_fin');
            $table->string('orientation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annotation_patrons');
    }
};
