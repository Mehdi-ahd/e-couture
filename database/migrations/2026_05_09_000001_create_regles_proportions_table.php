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
            $table->foreignUuid('type_mesure_id')
                  ->constrained('type_mesures')
                  ->restrictOnDelete();
            $table->string('nom');
            $table->decimal('coefficient', 10, 6);
            $table->decimal('offset', 10, 6);
            $table->string('source_metier');
            $table->unsignedInteger('version')->default(1);
            $table->boolean('est_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regles_proportions');
    }
};
