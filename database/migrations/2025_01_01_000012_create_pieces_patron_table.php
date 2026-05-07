<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pieces_patron', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->string('nom');
            $table->integer('ordre');
            $table->jsonb('donnees_geometriques');
            $table->foreignId('patron_id')
                  ->constrained('patrons')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pieces_patron');
    }
};
