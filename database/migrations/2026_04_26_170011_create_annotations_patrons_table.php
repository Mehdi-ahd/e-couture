<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annotations_patrons', function (Blueprint $table) {
            $table->id();
            $table->char('external_id', 36)->unique('uniq_annotations_patrons_external_id');
            $table->foreignId('piece_patron_id')->constrained('pieces_patrons')->cascadeOnDelete();
            $table->foreignId('type_mensuration_id')->constrained('types_mensurations');
            $table->string('label', 100);
            $table->string('position_depart', 50);
            $table->string('position_fin', 50);
            $table->string('orientation', 20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annotations_patrons');
    }
};
