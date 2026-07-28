<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_vetement_mesures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_vetement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('type_mesure_id')->constrained()->restrictOnDelete();
            $table->boolean('est_obligatoire')->default(true);
            $table->timestamps();

            $table->unique(['type_vetement_id', 'type_mesure_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('type_vetement_mesures');
    }
};
