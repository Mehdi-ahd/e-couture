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
            $table->string('label');
            $table->string('position_depart');
            $table->string('position_fin');
            $table->string('orientation');
            $table->foreignId('piece_patron_id')
                  ->constrained('pieces_patron')
                  ->cascadeOnDelete();
            $table->foreignId('type_mesure_id')
                  ->nullable()
                  ->constrained('type_mesures')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annotation_patrons');
    }
};
